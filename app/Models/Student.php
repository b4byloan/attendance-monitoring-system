<?php

namespace App\Models;

use App\Observers\StudentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(StudentObserver::class)]
class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'lrn',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'section_id',
        'qr_code',
        'photo_path',
        'status',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function getFullNameAttribute(): string
    {
        return collect([$this->first_name, $this->middle_name, $this->last_name, $this->suffix])
            ->filter()
            ->implode(' ');
    }
}