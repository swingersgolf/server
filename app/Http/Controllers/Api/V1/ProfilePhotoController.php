<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProfilePhotoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProfilePhotoController extends Controller
{
    public function generateUploadUrl(Request $request): JsonResponse
    {
        $user = $request->user();

        // Define the S3 file path for the user's photo
        $fileName = "profile-photos/{$user->id}/" . uniqid() . '.jpg';
        $disk = Storage::disk('s3');

        // Generate a pre-signed URL for upload
        $uploadUrl = $disk->temporaryUrl(
            $fileName,
            now()->addMinutes(15), // URL validity
            ['ContentType' => 'image/jpeg']
        );

        return response()->json([
            'upload_url' => $uploadUrl,
            'file_path' => $fileName,
        ], ResponseAlias::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $userProfile = $user->profile;

        $request->validate([
            'file_path' => 'required|string', // Validate the file path returned by the client
        ]);

        // Save the new file path in the database
        $userProfile->profile_photo_path = $request->input('file_path');
        $userProfile->save();

        return response()->json([
            'message' => 'Profile photo uploaded successfully.',
            'photo_url' => Storage::disk('s3')->url($userProfile->profile_photo_path),
        ], ResponseAlias::HTTP_OK);
    }

    public function destroy(): JsonResponse
    {
        $user = auth()->user();
        $userProfile = $user->userProfile;

        if (! $userProfile->profile_photo_path) {
            return response()->json(['message' => 'No profile photo to delete.'], ResponseAlias::HTTP_NOT_FOUND);
        }

        // Delete the photo
        Storage::disk('s3')->delete($userProfile->profile_photo_path);
        $userProfile->profile_photo_path = null;
        $userProfile->save();

        return response()->json(['message' => 'Profile photo deleted successfully.'], ResponseAlias::HTTP_OK);
    }
}
