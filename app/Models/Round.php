<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'when',
        'spots',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function preferences(): BelongsToMany
    {
        return $this->belongsToMany(Preference::class)->withPivot('status');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function scopeDateRange($query, $start = null, $end = null)
    {
        if ($start) {
            $query->where('when', '>=', $start);
        }
        if ($end) {
            $query->where('when', '<=', $end);
        }

        return $query;
    }
}
