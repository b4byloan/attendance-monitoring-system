<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Guardian;
use Illuminate\Support\Facades\Auth;

class GuardianObserver
{
    public function created(Guardian $guardian): void
    {
        $this->log('guardian_created', "Created guardian {$guardian->first_name} {$guardian->last_name}.", $guardian);
    }

    public function updated(Guardian $guardian): void
    {
        $this->log('guardian_updated', "Updated guardian {$guardian->first_name} {$guardian->last_name}.", $guardian);
    }

    public function deleted(Guardian $guardian): void
    {
        $this->log('guardian_deleted', "Deleted guardian {$guardian->first_name} {$guardian->last_name}.", $guardian);
    }

    private function log(string $action, string $description, Guardian $guardian): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => 'Guardian',
            'subject_id' => $guardian->id,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}