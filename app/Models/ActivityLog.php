<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function actionStyle(): array
    {
        return match (true) {
            str_contains($this->action, 'login') => ['indigo', 'unlock'],
            str_contains($this->action, 'logout') => ['gray', 'lock'],
            str_ends_with($this->action, '_created') => ['green', 'plus'],
            str_ends_with($this->action, '_updated') => ['blue', 'pencil'],
            str_ends_with($this->action, '_deleted') => ['red', 'trash'],
            str_contains($this->action, 'approved') => ['green', 'check'],
            str_contains($this->action, 'rejected') => ['red', 'x'],
            str_contains($this->action, 'requested') => ['amber', 'flag'],
            str_contains($this->action, 'downloaded') => ['blue', 'download'],
            default => ['gray', 'circle'],
        };
    }

    public function subjectUrl(): ?string
    {
        if (! $this->subject_type || ! $this->subject_id) {
            return null;
        }

        return match ($this->subject_type) {
            'Student' => route('admin.students.show', $this->subject_id),
            'Guardian' => route('admin.guardians.show', $this->subject_id),
            'Section' => route('admin.sections.show', $this->subject_id),
            default => null,
        };
    }
}
