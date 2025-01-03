<?php

namespace App\Http\Resources\Api\V1;

use App\Services\ProfilePhotoServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{

    protected ProfilePhotoServiceInterface $profilePhotoService;

    public function __construct($resource, ProfilePhotoServiceInterface $profilePhotoService)
    {
        parent::__construct($resource);
        $this->profilePhotoService = $profilePhotoService;
    }

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
            'photo' => $this->profilePhotoService->getPresignedUrl($this->user->id)
        ];
    }
}
