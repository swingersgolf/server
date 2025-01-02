<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicAccountResource;
use App\Models\User;
use App\Services\ProfilePhotoService;
use Illuminate\Http\JsonResponse;

class PublicAccountController extends Controller
{
    public function show($userId): JsonResponse|PublicAccountResource
    {

        // Find the user by ID
        $user = User::find($userId);

        // Check if user exists
        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        // Return the public profile resource
        $profilePhotoService = app(ProfilePhotoService::class);
        return new PublicAccountResource($user, $profilePhotoService);
    }
}
