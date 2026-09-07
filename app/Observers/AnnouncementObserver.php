<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class AnnouncementObserver
{
    public function created(Announcement $announcement): void
    {
        $this->log('announcement_created', "Created announcement \"{$announcement->title}\".", $announcement);
    }

    public function updated(Announcement $announcement): void
    {
        $this->log('announcement_updated', "Updated announcement \"{$announcement->title}\".", $announcement);
    }

    public function deleted(Announcement $announcement): void
    {
        $this->log('announcement_deleted', "Deleted announcement \"{$announcement->title}\".", $announcement);
    }

    private function log(string $action, string $description, Announcement $announcement): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => 'Announcement',
            'subject_id' => $announcement->id,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}