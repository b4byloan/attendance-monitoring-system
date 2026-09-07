<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    use LoadsNotificationBell;

    private const DISK = 'local';
    private const FOLDER = 'attendance-system-backup';

    public function index()
    {
        $backups = collect(Storage::disk(self::DISK)->files(self::FOLDER))
            ->filter(fn ($path) => str_ends_with($path, '.zip'))
            ->map(function ($path) {
                $filename = basename($path);

                $creationLog = ActivityLog::where('action', 'backup_created')
                    ->where('description', 'like', "%{$filename}%")
                    ->with('user')
                    ->first();

                return [
                    'filename' => $filename,
                    'size' => Storage::disk(self::DISK)->size($path),
                    'created_at' => Carbon::createFromTimestamp(Storage::disk(self::DISK)->lastModified($path)),
                    'created_by' => $creationLog->user->name ?? 'Unknown',
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        return view('admin.backups.index', compact('backups'))->with($this->notificationBellData());
    }

    public function store(Request $request)
    {
        Artisan::call('backup:run', ['--only-db' => true]);

        $latestFile = $this->latestBackupFile();

        if (! $latestFile) {
            return back()->withErrors(['backup' => 'The backup command ran but no file appeared afterward. Check storage/logs/laravel.log - this usually means mysqldump could not be found.']);
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'backup_created',
            'description' => 'Created backup: ' . basename($latestFile),
            'subject_type' => 'Backup',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Backup created.');
    }

    public function download(Request $request, string $filename)
    {
        if (! $this->isValidBackupFilename($filename)) {
            abort(404);
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'backup_downloaded',
            'description' => "Downloaded backup: {$filename}",
            'subject_type' => 'Backup',
            'ip_address' => $request->ip(),
        ]);

        return Storage::disk(self::DISK)->download(self::FOLDER . '/' . $filename);
    }

    public function destroy(Request $request, string $filename)
    {
        if (! $this->isValidBackupFilename($filename)) {
            abort(404);
        }

        Storage::disk(self::DISK)->delete(self::FOLDER . '/' . $filename);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'backup_deleted',
            'description' => "Deleted backup: {$filename}",
            'subject_type' => 'Backup',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Backup deleted.');
    }

    public function restore(Request $request, string $filename)
    {
        if (! $this->isValidBackupFilename($filename)) {
            abort(404);
        }

        $request->validate([
            'confirmation' => ['required', 'in:RESTORE'],
        ]);

        $zipPath = Storage::disk(self::DISK)->path(self::FOLDER . '/' . $filename);

        // 1. Validate the file before touching anything.
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return back()->withErrors(['restore' => 'This backup file could not be opened - it may be corrupted. Nothing was changed.']);
        }

        $sqlFileName = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (str_ends_with($entry, '.sql')) {
                $sqlFileName = $entry;
                break;
            }
        }

        if (! $sqlFileName) {
            $zip->close();
            return back()->withErrors(['restore' => 'This backup does not contain a database dump file. Nothing was changed.']);
        }

        $sqlContent = $zip->getFromName($sqlFileName);
        $zip->close();

        if (! $sqlContent || trim($sqlContent) === '') {
            return back()->withErrors(['restore' => 'The database dump inside this backup is empty. Nothing was changed.']);
        }

        if (! str_contains($sqlContent, 'CREATE TABLE') || ! str_contains($sqlContent, '`users`')) {
            return back()->withErrors(['restore' => 'This does not look like a valid backup for this system - expected tables are missing. Nothing was changed.']);
        }

        // 2. Safety net: back up the CURRENT state first, before changing anything.
        Artisan::call('backup:run', ['--only-db' => true]);
        $safetyBackup = $this->latestBackupFile();

        if (! $safetyBackup) {
            return back()->withErrors(['restore' => 'Could not create a safety backup of your current data, so the restore was not attempted. Check storage/logs/laravel.log.']);
        }

        $safetyFilename = basename($safetyBackup);

        // 3. Perform the restore.
        $statements = $this->splitSqlStatements($sqlContent);
        $executed = 0;

        try {
            foreach ($statements as $statement) {
                if (trim($statement) === '') {
                    continue;
                }
                DB::unprepared($statement);
                $executed++;
            }
        } catch (\Throwable $e) {
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'backup_restore_failed',
                'description' => "Restore from {$filename} failed after {$executed} of " . count($statements) . " steps. Safety backup: {$safetyFilename}. Error: " . $e->getMessage(),
                'subject_type' => 'Backup',
                'ip_address' => $request->ip(),
            ]);

            return back()->withErrors(['restore' =>
                "Restore failed partway through ({$executed} of " . count($statements) . " steps completed) - the database may now be inconsistent. " .
                "A safety backup of your data from just before this attempt was saved as \"{$safetyFilename}\" - restore that one now to recover. " .
                "Error detail: " . $e->getMessage()
            ]);
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'backup_restored',
            'description' => "Restored from {$filename}. Safety backup of prior data saved as {$safetyFilename}.",
            'subject_type' => 'Backup',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Restore complete. A safety backup of your data from just before this - \"{$safetyFilename}\" - was saved automatically in case you need to undo this.");
    }

    private function isValidBackupFilename(string $filename): bool
    {
        return collect(Storage::disk(self::DISK)->files(self::FOLDER))
            ->map(fn ($p) => basename($p))
            ->contains($filename);
    }

    private function latestBackupFile(): ?string
    {
        return collect(Storage::disk(self::DISK)->files(self::FOLDER))
            ->filter(fn ($path) => str_ends_with($path, '.zip'))
            ->sortByDesc(fn ($path) => Storage::disk(self::DISK)->lastModified($path))
            ->first();
    }

    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';

        foreach (explode("\n", $sql) as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            $buffer .= $line . "\n";

            if (str_ends_with($trimmed, ';')) {
                $statements[] = $buffer;
                $buffer = '';
            }
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }
}