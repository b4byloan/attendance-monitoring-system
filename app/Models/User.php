<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

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

    /**
     * The OTP expiry is stored in UTC so it stays correct no matter which
     * timezone the app is configured with when the code is created or checked.
     */
    protected function otpExpiresAt(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value, 'UTC') : null,
            set: fn ($value) => $value === null
                ? null
                : Carbon::instance($value instanceof \DateTimeInterface ? $value : Carbon::parse($value))->utc(),
        );
    }

    public function advisedSections()
    {
        return $this->hasMany(Section::class, 'adviser_id');
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_user')->withTimestamps();
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_user')->withTimestamps();
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

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'sub_admin' => 'sub-admin.dashboard',
            'teacher' => 'sub-admin.dashboard',
            'parent' => 'parent.dashboard',
            'guard' => 'guard.dashboard',
            'student' => 'student.dashboard',
            default => 'login',
        };
    }

    /**
     * Use the school-branded reset mail instead of Laravel's default one.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSubAdmin(): bool
    {
        // Legacy role: Sub-Admins are now shown and treated as Teachers.
        return $this->role === 'sub_admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Teachers (including legacy Sub-Admin accounts) handle class sections
     * and subjects.
     */
    public function isTeachingStaff(): bool
    {
        return $this->isSubAdmin() || $this->isTeacher();
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isGuard(): bool
    {
        return $this->role === 'guard';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function roleLabel(): string
    {
        if ($this->role === 'sub_admin') {
            return 'Teacher';
        }

        return self::roleOptions()[$this->role] ?? ucwords(str_replace('_', ' ', (string) $this->role));
    }

    /** @return array<string, string> */
    public static function roleOptions(): array
    {
        return [
            'admin' => 'Admin',
            'teacher' => 'Teacher',
            'guard' => 'Guard',
            'parent' => 'Guardian',
            'student' => 'Student',
        ];
    }

    /** @return array<int, string> */
    public static function teachingRoles(): array
    {
        return ['sub_admin', 'teacher'];
    }
}
