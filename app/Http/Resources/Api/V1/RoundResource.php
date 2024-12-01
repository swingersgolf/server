<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'time_range' => $this->time_range,
            'course' => $this->course ? $this->course->name : null,
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
                    'status' => $user->pivot->status,
                ];
            }),
            // Count only the golfers with accepted status
            'golfer_count' => $this->users->filter(function ($user) {
                return $user->pivot->status === 'accepted'; // Adjust 'accepted' to your specific status value
            })->count(),
            'group_size' => $this->group_size,
            'host_id' => $this->host_id,
        ];
    }
}
