<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\User;
use App\Models\UserProfile;
use Tests\TestCase;

class UserProfileControllerTest extends TestCase
{
    public function test_it_updates_user_profile(): void
    {
        $user = User::factory()->create();
        $userProfile = UserProfile::first();
        $userProfile->update(['handicap' => 5]);

        $this->assertDatabaseHas('user_profiles', $userProfile->toArray());

        $payload = [
            'handicap' => 10
        ];

        $this->actingAs($user)->patch(route('api.v1.user-profile.update'), $payload)
            ->assertSuccessful();

        $expected = array_replace($userProfile->toArray(), $payload);
        $this->assertDatabaseHas('user_profiles', $expected);
    }
}
