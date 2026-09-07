<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function advisedSections()
    {
        return $this->hasMany(Section::class, 'adviser_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'recorded_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function guardian()
    {
        return $this->hasOne(Guardian::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSubAdmin(): bool
    {
        return $this->role === 'sub_admin';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isGuard(): bool
    {
        return $this->role === 'guard';
    }
}