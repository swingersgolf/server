<?php

namespace App\Http\Resources\Api\V1;

use App\Services\ProfilePhotoService;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicUserProfileResource extends JsonResource
{
    protected ProfilePhotoService $profilePhotoService;

    public function __construct($resource, ProfilePhotoService $profilePhotoService)
    {
        parent::__construct($resource);
        $this->profilePhotoService = $profilePhotoService;
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'photo' => $this->profilePhotoService->getPresignedUrl($this->id),
            'birthdate' => $this->date_of_birth,
            'preferences' => $this->preferences->map(function ($preference) {
                return [
                    'id' => $preference->id,
                    'name' => $preference->name,
                    'status' => $preference->pivot->status,
                ];
            }),
        ];
    }
}
