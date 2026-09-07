<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;

class SectionObserver
{
    public function created(Section $section): void
    {
        $this->log('section_created', "Created section {$section->name} ({$section->school_year}).", $section);
    }

    public function updated(Section $section): void
    {
        $this->log('section_updated', "Updated section {$section->name}.", $section);
    }

    public function deleted(Section $section): void
    {
        $this->log('section_deleted', "Deleted section {$section->name}.", $section);
    }

    private function log(string $action, string $description, Section $section): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => 'Section',
            'subject_id' => $section->id,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}