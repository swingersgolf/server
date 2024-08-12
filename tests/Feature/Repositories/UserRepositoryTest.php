<?php

namespace Tests\Feature\Repositories;

use App\Models\User;
use App\Models\UserProfile;
use App\Repositories\UserRepository;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository();
    }

    public function test_it_updates_profile_handicap()
    {
        $user = User::factory()->create();

        $attributes = [
            'handicap' => 8.2,
        ];

        $this->userRepository->update($user->id, $attributes);
        $this->assertDatabaseHas('user_profiles', $attributes);
    }

    public function test_it_updates_account_name()
    {
        $user = User::factory()->create();

        $attributes = [
            'name' => "new name",
        ];

        $this->userRepository->update($user->id, $attributes);
        $this->assertDatabaseHas('users', $attributes);
    }
}
