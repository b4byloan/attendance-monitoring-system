<?php

namespace App\Services;

use App\Mail\AttendanceRecorded;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AttendanceNotifier
{
    /**
     * Email every active guardian that has an address. Failures are logged and
     * swallowed so a mail problem can never roll back a saved attendance record.
     *
     * @param  array<string, string|null>  $details
     */
    public function notifyGuardians(Student $student, array $details): void
    {
        $guardians = $student->guardians()
            ->where('status', 'active')
            ->whereNotNull('email')
            ->get();

        foreach ($guardians as $guardian) {
            try {
                Mail::to($guardian->email)->queue(new AttendanceRecorded(
                    studentName: $student->full_name,
                    guardianName: trim($guardian->first_name.' '.$guardian->last_name),
                    date: (string) ($details['date'] ?? now()->format('F j, Y')),
                    time: (string) ($details['time'] ?? now()->format('h:i A')),
                    subjectName: $details['subject'] ?? null,
                    status: (string) ($details['status'] ?? 'Present'),
                    recordedBy: $details['recorded_by'] ?? null,
                ));
            } catch (Throwable $exception) {
                Log::warning('Attendance email could not be queued.', [
                    'student_id' => $student->id,
                    'guardian_id' => $guardian->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
