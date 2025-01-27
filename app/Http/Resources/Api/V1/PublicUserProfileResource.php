<?php

namespace App\Http\Resources\Api\V1;

use App\Services\ProfilePhotoServiceInterface;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicUserProfileResource extends JsonResource
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
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
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
