<?php

namespace App\Services;

interface ProfilePhotoServiceInterface
{
    public function putPresignedUrl(string $userId): string;

    public function getPresignedUrl(string $userId): string;

}
