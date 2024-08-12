<?php

namespace App\Repositories\Interfaces;
interface UserRepositoryInterface
{
    public function update(string $userId, array $attributes);
}
