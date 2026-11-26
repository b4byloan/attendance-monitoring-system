<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subject_user')->withTimestamps();
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(SubjectAttendanceRecord::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
