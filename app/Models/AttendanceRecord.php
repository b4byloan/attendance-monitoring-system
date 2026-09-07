<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'type',
        'recorded_by',
        'source',
        'device_identifier',
        'scanned_at',
        'attendance_date',
        'synced_at',
        'sms_status',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'attendance_date' => 'date',
        'synced_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function smsNotifications()
    {
        return $this->hasMany(SmsNotification::class);
    }
    public function refreshSmsStatus(): void
    {
        $statuses = $this->smsNotifications()->pluck('status');

        if ($statuses->isEmpty()) {
            $this->update(['sms_status' => 'not_applicable']);
            return;
        }

        if ($statuses->contains('pending')) {
            return;
        }

        $this->update([
            'sms_status' => $statuses->contains('failed') ? 'failed' : 'sent',
        ]);
    }
}