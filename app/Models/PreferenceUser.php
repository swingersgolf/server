<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreferenceUser extends Model
{
    use HasFactory;

    protected $table = 'preference_user';

    protected $fillable = [
        'preference_id',
        'user_id',
        'status',
    ];

    // Define relationships if needed
    public function preference()
    {
        return $this->belongsTo(Preference::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
