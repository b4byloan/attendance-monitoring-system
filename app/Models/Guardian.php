<?php

namespace App\Models;

use App\Observers\GuardianObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(GuardianObserver::class)]
class Guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'relationship_to_student',
        'address',
        'status',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'guardian_student');
    }

    public function smsNotifications()
    {
        return $this->hasMany(SmsNotification::class);
    }
}