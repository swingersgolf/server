<?php

namespace App\Models;

use App\Traits\UpdatesFillablesOnly;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory, UpdatesFillablesOnly;

    protected $fillable = [
        'user_id',
        'handicap',
        'dob',
        'postal_code',
    ];
}
