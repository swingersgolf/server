<?php

namespace App\Models;

use App\Http\Filters\V1\RoundFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'when',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class);
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
