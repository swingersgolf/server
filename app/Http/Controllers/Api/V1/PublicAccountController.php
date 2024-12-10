<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicAccountResource;
use App\Models\User;
use Illuminate\Http\Request;

class PublicAccountController extends Controller
{
/**
     * Display the public profile of the specified user.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($userId)
    {
        // Find the user by ID
        $user = User::find($userId);

        // Check if user exists
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        // Return the public profile resource
        return new PublicAccountResource($user);
    }
}
