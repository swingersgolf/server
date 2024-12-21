<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Aws\S3\S3Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProfilePhotoController extends Controller
{
    private $s3Client;

    private $bucket;

    public function __construct()
    {
        $this->s3Client = new S3Client([
            'region' => config('filesystems.disks.s3.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);

        $this->bucket = config('filesystems.disks.s3.bucket');
    }

    public function get(Request $request): JsonResponse
    {
        $request->validate([
            'file_name' => 'required|string',
        ]);

        $filePath = 'profile_photos/'.$request->file_name;

        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $filePath,
        ]);

        $presignedUrl = $this->s3Client->createPresignedRequest($command, '+20 minutes')->getUri();

        return response()->json(['url' => (string) $presignedUrl]);
    }

    public function put(Request $request): JsonResponse
    {
        $request->validate([
            'file_name' => 'required|string',
            'file_type' => 'required|string',
        ]);

        $filePath = 'profile_photos/'.$request->file_name;

        $command = $this->s3Client->getCommand('PutObject', [
            'Bucket' => $this->bucket,
            'Key' => $filePath,
            'ContentType' => $request->file_type,
        ]);

        $presignedUrl = $this->s3Client->createPresignedRequest($command, '+20 minutes')->getUri();

        return response()->json(['url' => (string) $presignedUrl]);
    }

    public function foo(Request $request): JsonResponse
    {
        $request->validate([
            'file_name' => 'required|string',
            'file_type' => 'required|string',
            'operation' => 'required|in:get,put',
        ]);

        $bucket = config('filesystems.disks.s3.bucket');
        $filePath = 'profile_photos/'.$request->file_name;

        $s3Client = new S3Client([
            'region' => config('filesystems.disks.s3.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);

        $commandOptions = [
            'Bucket' => $bucket,
            'Key' => $filePath,
            'ContentType' => $request->file_type,
        ];

        if ($request->operation === 'put') {
            $commandOptions['ACL'] = 'public-read'; // Set as required
        }

        $command = $s3Client->getCommand(
            $request->operation === 'put' ? 'PutObject' : 'GetObject',
            $commandOptions
        );

        $presignedUrl = $s3Client->createPresignedRequest($command, '+20 minutes')->getUri();

        return response()->json(['url' => (string) $presignedUrl]);
    }

    public function generateUploadUrl(Request $request): JsonResponse
    {
        $user = $request->user();

        // Define the S3 file path for the user's photo
        $fileName = 'test.jpg';
        $disk = Storage::disk('s3');

        // Generate a pre-signed URL for upload
        $uploadUrl = $disk->temporaryUrl(
            $fileName,
            now()->addMinutes(15), // URL validity
            ['ResponseContentType' => 'image/jpeg']
        );

        return response()->json([
            'upload_url' => $uploadUrl,
            'file_path' => $fileName,
        ], ResponseAlias::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $userProfile = $user->userProfile;

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

    private function getExtensionFromContentType(string $contentType): string
    {
        return match ($contentType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => throw new \InvalidArgumentException('Unsupported content type: '.$contentType),
        };
    }
}
