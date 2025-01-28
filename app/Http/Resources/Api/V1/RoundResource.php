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
        $authUser = $request->user();

        $userLatitude = $authUser?->userProfile?->latitude;
        $userLongitude = $authUser?->userProfile?->longitude;

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
            'date' => $this->date,
            'time_range' => $this->time_range,
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
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'status' => $user->pivot->status,
                ];
            }),
            // Count only the golfers with accepted status
            'golfer_count' => $this->users->filter(function ($user) {
                return $user->pivot->status === 'accepted'; // Adjust 'accepted' to your specific status value
            })->count(),
            'group_size' => $this->group_size,
            'host_id' => $this->host_id,
            'distance' => $distance, // Include the calculated distance
            'message_group_id' => $this->messageGroup->id,
        ];
    }
}
