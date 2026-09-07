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

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}