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

    /**
     * Deleting a guardian record also removes the portal account created for it,
     * so the email address can be used to register again.
     */
    public function deleting(Guardian $guardian): void
    {
        $user = $guardian->user;

        if ($user && $user->role === 'parent') {
            $user->delete();
        }
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
