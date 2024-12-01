<?php

namespace App\Http\Resources\Api\V1;

use App\Services\GeocodingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoundResource extends JsonResource
{
    protected GeocodingService $geocodingService;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->geocodingService = new GeocodingService(); // Initialize the GeocodingService
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the latitude and longitude for user and course
        $userLatitude = $this->users->first()->userProfile->latitude ?? null;
        $userLongitude = $this->users->first()->userProfile->longitude ?? null;
        $courseLatitude = $this->course->latitude ?? null;
        $courseLongitude = $this->course->longitude ?? null;

        // Initialize distance to null
        $distance = null;

        // Check if we have both user and course latitude and longitude
        if ($userLatitude && $userLongitude && $courseLatitude && $courseLongitude) {
            // Calculate the distance using the latitude and longitude
            $distance = $this->geocodingService->calculateDistance(
                (float) $userLatitude,
                (float) $userLongitude,
                (float) $courseLatitude,
                (float) $courseLongitude
            );
        }

        return [
            'id' => $this->id,
            'when' => $this->when,
            'course' => $this->course ? $this->course->course_name : null,
            'preferences' => $this->preferences->map(function ($preference) {
                return [
                    'id' => $preference->id,
                    'name' => $preference->name,
                    'status' => $preference->pivot->status,
                ];
            }),
            'golfers' => $this->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            }),
            'golfer_count' => $this->users->count(),
            'spots' => $this->spots,
            'distance' => $distance, // Include the calculated distance
        ];
    }
}
