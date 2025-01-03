<?php

namespace App\Services;

use Aws\S3\S3Client;

class AwsProfilePhotoService implements ProfilePhotoServiceInterface
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

    public function getPresignedUrl(string $userId): string
    {
        $filePath = 'profile_photos/'.$userId.'.jpg';

        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $filePath,
        ]);

        return $this->s3Client->createPresignedRequest($command, '+20 minutes')->getUri();
    }

    public function putPresignedUrl(string $userId): string
    {
        $fileType = 'image/jpeg';
        $filePath = 'profile_photos/'.$userId.'.jpg';

        $command = $this->s3Client->getCommand('PutObject', [
            'Bucket' => $this->bucket,
            'Key' => $filePath,
            'ContentType' => $fileType,
        ]);

        return $this->s3Client->createPresignedRequest($command, '+20 minutes')->getUri();
    }
}
