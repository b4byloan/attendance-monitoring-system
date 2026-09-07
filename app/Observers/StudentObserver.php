<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class StudentObserver
{
    public function created(Student $student): void
    {
        $this->log('student_created', "Created student {$student->full_name} (LRN: {$student->lrn}).", $student);
    }

    public function updated(Student $student): void
    {
        $this->log('student_updated', "Updated student {$student->full_name}.", $student);
    }

    public function deleted(Student $student): void
    {
        $this->log('student_deleted', "Deleted student {$student->full_name} (LRN: {$student->lrn}).", $student);
    }

    private function log(string $action, string $description, Student $student): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => 'Student',
            'subject_id' => $student->id,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}