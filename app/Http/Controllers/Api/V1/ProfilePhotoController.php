<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProfilePhotoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProfilePhotoController extends Controller
{
    public function store(ProfilePhotoRequest $request): JsonResponse
    {
        $user = $request->user();
        $userProfile = $user->userProfile;

        // Delete old photo if exists
        if ($userProfile->profile_photo_path) {
            Storage::disk('s3')->delete($userProfile->profile_photo_path);
        }

        // Store new photo
        $path = $request->file('photo')->store('profile-photos', 's3');
        $userProfile->profile_photo_path = $path;
        $userProfile->save();

        return response()->json([
            'message' => 'Profile photo updated successfully.',
            'photo_url' => Storage::disk('s3')->url($path),
        ], ResponseAlias::HTTP_OK);
    }

    public function destroy(): JsonResponse
    {
        $user = auth()->user();
        $userProfile = $user->userProfile;

        if (!$userProfile->profile_photo_path) {
            return response()->json(['message' => 'No profile photo to delete.'], ResponseAlias::HTTP_NOT_FOUND);
        }

        // Delete the photo
        Storage::disk('s3')->delete($userProfile->profile_photo_path);
        $userProfile->profile_photo_path = null;
        $userProfile->save();

        return response()->json(['message' => 'Profile photo deleted successfully.'], ResponseAlias::HTTP_OK);
    }
}
