<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicUserProfileResource;
use App\Models\User;
use App\Services\ProfilePhotoService;
use Illuminate\Http\JsonResponse;

class PublicUserProfileController extends Controller
{
    public function show($userId): JsonResponse|PublicUserProfileResource
    {

        // Find the user by ID
        $user = User::find($userId);

        // Check if user exists
        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $profilePhotoService = app(ProfilePhotoService::class);
        // Return the public profile resource
        return new PublicUserProfileResource($user, $profilePhotoService);
    }
}
