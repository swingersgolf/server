<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'handicap' => $this->handicap,
            'postalCode' => $this->postal_code,
            'latitude' => $this->latitude,  // Add latitude to the response
            'longitude' => $this->longitude,  // Add longitude to the response
        ];
    }
}
