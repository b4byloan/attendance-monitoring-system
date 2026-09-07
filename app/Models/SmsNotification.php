<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_record_id',
        'guardian_id',
        'phone_number',
        'message',
        'status',
        'provider_response',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }
}