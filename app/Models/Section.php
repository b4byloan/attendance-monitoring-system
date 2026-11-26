<?php

namespace App\Models;

use App\Observers\SectionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(SectionObserver::class)]
class Section extends Model
{
    use HasFactory;

    /**
     * Grade levels handled by the school — the section form only offers these.
     *
     * @var list<string>
     */
    public const GRADE_LEVELS = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];

    /**
     * Map loosely typed grade levels ("7", "grade 8") to the canonical option,
     * or null when the value cannot be mapped.
     */
    public static function normalizeGradeLevel(string $value): ?string
    {
        $value = trim($value);

        foreach (self::GRADE_LEVELS as $gradeLevel) {
            if (strcasecmp($value, $gradeLevel) === 0) {
                return $gradeLevel;
            }
        }

        if (ctype_digit($value)) {
            $candidate = 'Grade '.$value;

            if (in_array($candidate, self::GRADE_LEVELS, true)) {
                return $candidate;
            }
        }

        return null;
    }

    protected $fillable = [
        'name',
        'grade_level',
        'school_year',
        'max_capacity',
        'status',
        'adviser_id',
    ];

    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'section_user')->withTimestamps();
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
