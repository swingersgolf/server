<?php

namespace App\Repositories\Interfaces;
interface UserProfileRepositoryInterface
{
    public function update(string $userId, array $attributes);
}
