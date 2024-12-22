<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Aws\S3\S3Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilePhotoController extends Controller
{
    private S3Client $s3Client;

    private mixed $bucket;

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
        $userId = Auth::id();
        $filePath = 'profile_photos/'.$userId.'.jpg';

        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $filePath,
        ]);

        $presignedUrl = $this->s3Client->createPresignedRequest($command, '+20 minutes')->getUri();

        return response()->json(['url' => (string) $presignedUrl]);
    }

    public function put(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $fileType = 'image/jpeg';
        $filePath = 'profile_photos/'.$userId.'.jpg';

        $command = $this->s3Client->getCommand('PutObject', [
            'Bucket' => $this->bucket,
            'Key' => $filePath,
            'ContentType' => $fileType,
        ]);

        $presignedUrl = $this->s3Client->createPresignedRequest($command, '+20 minutes')->getUri();

        return response()->json(['url' => (string) $presignedUrl]);
    }
}
