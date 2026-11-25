<?php

namespace App\Models;

use App\Observers\StudentObserver;
use App\Services\QrCodeService;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'user_id',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function subjectAttendanceRecords()
    {
        return $this->hasMany(SubjectAttendanceRecord::class);
    }

    public function linkRequests(): HasMany
    {
        return $this->hasMany(GuardianLinkRequest::class);
    }

    /**
     * Find a student from the text decoded off a QR code or typed by a
     * hardware scanner. Scanners frequently append a line break, and some
     * send letters in the wrong case, so the lookup tolerates both before
     * declaring the code unrecognised.
     */
    public static function findByQrCode(string $scannedToken): ?self
    {
        $token = preg_replace('/[\x00-\x1F\x7F]|\x{200B}|\x{FEFF}/u', '', $scannedToken) ?? $scannedToken;
        $token = trim($token);

        if ($token === '') {
            return null;
        }

        return static::query()->where('qr_code', $token)->first()
            ?? static::query()->whereRaw('LOWER(qr_code) = ?', [mb_strtolower($token)])->first();
    }

    /**
     * Find an active student record that matches the details a student or
     * guardian entered. LRN, first/last name, section and the section's
     * grade level must match; provided middle names and suffixes must match too.
     */
    public static function matchSchoolRecord(
        string $lrn,
        string $firstName,
        string $lastName,
        int $sectionId,
        string $gradeLevel,
        ?string $middleName = null,
        ?string $suffix = null,
    ): ?self {
        $student = static::query()
            ->with('section')
            ->where('lrn', $lrn)
            ->first();

        if (! $student || $student->status !== 'active') {
            return null;
        }

        $normalize = fn (string $value): string => mb_strtolower(preg_replace('/\s+/', ' ', trim($value)) ?? '');

        $nameMatches = $normalize($student->first_name) === $normalize($firstName)
            && $normalize($student->last_name) === $normalize($lastName);

        $middleMatches = $middleName === null
            || $normalize((string) $student->middle_name) === $normalize($middleName);

        $suffixMatches = $suffix === null
            || $normalize((string) $student->suffix) === $normalize($suffix);

        $sectionMatches = (int) $student->section_id === $sectionId;
        $gradeMatches = $normalize((string) $student->section?->grade_level) === $normalize($gradeLevel);

        return $nameMatches && $middleMatches && $suffixMatches && $sectionMatches && $gradeMatches ? $student : null;
    }

    /**
     * Render this student's QR code as an inline PNG data URI for previews.
     */
    public function qrCodeDataUri(?int $size = null): string
    {
        return app(QrCodeService::class)->dataUri($this->qr_code, $size);
    }

    public function getFullNameAttribute(): string
    {
        return collect([$this->first_name, $this->middle_name, $this->last_name, $this->suffix])
            ->filter()
            ->implode(' ');
    }
}
