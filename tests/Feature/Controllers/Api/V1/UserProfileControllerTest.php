<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserProfileControllerTest extends TestCase
{
    #[DataProvider('validPayloads')]
    public function test_it_updates_user_profile_payloads($payload): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('api.v1.user-profile.update'), $payload)
            ->assertSuccessful();

        $this->assertDatabaseHas('user_profiles', $payload);
    }

    public static function validPayloads()
    {
        return [
            'handicap' => [
                'payload' => ['handicap' => '12'],
            ],
        ];
    }
}
