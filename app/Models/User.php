<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Observers\UserObserver;
use App\Traits\UpdatesFillablesOnly;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, UpdatesFillablesOnly;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'birthdate',
        'email_verified_at',
        'expo_push_token',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function rounds(): BelongsToMany
    {
        return $this->belongsToMany(Round::class)
            ->withPivot('status')  // Include 'status' on the pivot
            ->withTimestamps();    // Adds created_at and updated_at for the pivot
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
  
    public function preferences(): BelongsToMany
    {
        return $this->belongsToMany(Preference::class)->withPivot('status');
    }
}
