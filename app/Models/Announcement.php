<?php

namespace App\Models;

use App\Observers\AnnouncementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(AnnouncementObserver::class)]
class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'section_id',
        'author_id',
        'publish_date',
        'expiration_date',
        'is_pinned',
        'status',
    ];

    protected $casts = [
        'publish_date' => 'date',
        'expiration_date' => 'date',
        'is_pinned' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopeVisible($query)
    {
        $today = now()->toDateString();

        return $query->where('status', 'published')
            ->whereDate('publish_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', $today);
            });
    }

    public function isSchoolWide(): bool
    {
        return is_null($this->section_id);
    }
}
