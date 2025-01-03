<?php

namespace App\Services;

class MockProfilePhotoService implements ProfilePhotoServiceInterface
{
    public function getPresignedUrl(string $userId): string
    {
        return 'https://example.test/geturl';
    }

    public function putPresignedUrl(string $userId): string
    {
        return 'https://example.test/puturl';
    }
}
