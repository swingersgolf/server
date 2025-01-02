<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ProfilePhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilePhotoController extends Controller
{
    private ProfilePhotoService $profilePhotoService;

    public function __construct(ProfilePhotoService $profilePhotoService)
    {
        $this->profilePhotoService = $profilePhotoService;
    }

    public function get(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $presignedUrl = $this->profilePhotoService->getPresignedUrl($userId);

        return response()->json(['url' => (string) $presignedUrl]);
    }

    public function put(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $presignedUrl = $this->profilePhotoService->putPresignedUrl($userId);

        return response()->json(['url' => (string) $presignedUrl]);
    }
}
