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
        'group_size',
        'host_id',
        'course_id',
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
        return $this->belongsToMany(User::class)
            ->withPivot('status')  // Include 'status' on the pivot
            ->withTimestamps();    // Adds created_at and updated_at for the pivot
    }

    // Relationship with the host user
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
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
