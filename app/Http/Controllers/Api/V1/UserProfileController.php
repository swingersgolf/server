<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserProfileUpdateRequest;
use App\Http\Resources\Api\V1\UserProfileResource;
use App\Services\GeocodingService;

class UserProfileController extends Controller
{
    protected GeocodingService $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService; // Inject GeocodingService
    }

    public function show(): UserProfileResource
    {
        $user = auth()->user();
        return new UserProfileResource($user->userProfile);
    }

    public function update(UserProfileUpdateRequest $request): UserProfileResource
    {
        $user = auth()->user();
        $validatedData = $request->validated();  // Get the validated data from the request

        // Check if a postal code is provided in the request
        if (isset($validatedData['postal_code'])) {
            // Get latitude and longitude from geocoding service
            $geocode = $this->geocodingService->geocodePostalCode($validatedData['postal_code']);
            // Merge postal code and geocoded latitude/longitude with existing validated data
            $validatedData['latitude'] = $geocode['lat'] ?? null;
            $validatedData['longitude'] = $geocode['lon'] ?? null;
        }

        // Update the user's profile with the validated data (only changes the necessary fields)
        $user->userProfile()->update($validatedData);

        return new UserProfileResource($user->userProfile);
    }
}
