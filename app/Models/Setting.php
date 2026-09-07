<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'school_name', 'school_logo_path', 'school_address', 'principal_name',
        'school_contact_number', 'school_email', 'academic_year', 'timezone',
        'school_start_time', 'school_end_time',
        'checkin_start_time', 'checkin_cutoff_time', 'checkout_start_time', 'checkout_cutoff_time',
        'late_threshold_minutes',
        'qr_expiration_days', 'qr_format', 'qr_default_size',
        'sms_provider', 'sms_sender_name', 'sms_enabled',
    ];

    protected $casts = [
        'sms_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'academic_year' => now()->year . '-' . (now()->year + 1),
        ]);
    }
}