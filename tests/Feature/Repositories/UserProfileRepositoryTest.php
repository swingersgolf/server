<?php

namespace Tests\Feature\Repositories;

use App\Models\User;
use App\Models\UserProfile;
use App\Repositories\UserProfileRepository;
use Tests\TestCase;

class UserProfileRepositoryTest extends TestCase
{
    private UserProfileRepository $userProfileRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userProfileRepository = new UserProfileRepository();
    }

    public function test_it_updates_profile_handicap()
    {
        $user = User::factory()->create();
        $userProfile = UserProfile::first();

        $this->assertDatabaseHas('user_profiles', $userProfile->toArray());

        $attributes = [
            'handicap' => 8.2,
        ];

        $this->userProfileRepository->update($user->id, $attributes);
        $this->assertDatabaseHas('user_profiles', $attributes);
    }

    public function test_it_updates_account_name()
    {
        $user = User::factory()->create();
        $userProfile = UserProfile::first();

        $this->assertDatabaseHas('user_profiles', $userProfile->toArray());

        $attributes = [
            'name' => "new name",
        ];

        $this->userProfileRepository->update($user->id, $attributes);
        $this->assertDatabaseHas('users', $attributes);
    }
}
