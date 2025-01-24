<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Round extends Model
{
    use HasFactory;

    // Enum values for time ranges
    const TIME_RANGE_EARLY_BIRD = 'early_bird';
    const TIME_RANGE_MORNING = 'morning';
    const TIME_RANGE_AFTERNOON = 'afternoon';
    const TIME_RANGE_TWILIGHT = 'twilight';

    protected $fillable = [
        'date',
        'time_range',
        'group_size',
        'host_id',
        'course_id',
        'message_group_id',
    ];

    protected $with = ['preferences'];

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
            $query->where('date', '>=', $start);
        }
        if ($end) {
            $query->where('date', '<=', $end);
        }

        return $query;
    }

    // Get all possible time range values
    public static function getTimeRanges()
    {
        return [
            self::TIME_RANGE_EARLY_BIRD,
            self::TIME_RANGE_MORNING,
            self::TIME_RANGE_AFTERNOON,
            self::TIME_RANGE_TWILIGHT,
        ];
    }
}
