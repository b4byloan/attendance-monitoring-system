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
            str_contains($this->action, 'login') => ['indigo', '🔓'],
            str_contains($this->action, 'logout') => ['gray', '🔒'],
            str_ends_with($this->action, '_created') => ['green', '＋'],
            str_ends_with($this->action, '_updated') => ['blue', '✎'],
            str_ends_with($this->action, '_deleted') => ['red', '🗑'],
            str_contains($this->action, 'approved') => ['green', '✓'],
            str_contains($this->action, 'rejected') => ['red', '✕'],
            str_contains($this->action, 'requested') => ['amber', '⚑'],
            str_contains($this->action, 'downloaded') => ['blue', '⬇'],
            default => ['gray', '•'],
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