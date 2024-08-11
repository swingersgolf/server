<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserProfileControllerTest extends TestCase
{
    #[DataProvider('validPayloads')] public function test_it_updates_user_profile_handicap($payload): void
    {
        $user = User::factory()->create();
        $userProfile = UserProfile::first();
        $userProfile->update(['handicap' => 5]);

        $this->assertDatabaseHas('user_profiles', $userProfile->toArray());

        $this->actingAs($user)->patch(route('api.v1.user-profile.update'), $payload)
            ->assertSuccessful();

        $expected = array_replace($userProfile->toArray(), $payload);
        $this->assertDatabaseHas('user_profiles', $expected);
    }

    public function test_it_cannot_update_user_id(): void
    {
        $user = User::factory()->create();

        $payload = [
            'user_id' => Str::uuid(),
        ];

        $this->actingAs($user)->patch(route('api.v1.user-profile.update'), $payload)
            ->assertSuccessful();

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
        ]);
    }

    #[DataProvider('invalidPayloads')] public function test_session_has_errors_when_payload_invalid($payload, $sessionError): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('api.v1.user-profile.update'), $payload)
            ->assertSessionHasErrors($sessionError);
    }

    public static function validPayloads()
    {
        return [
            'handicap can have no decimal' => [
                'payload' => ['handicap' => 10],
            ],
            'handicap can have one decimal' => [
                'payload' => ['handicap' => 10.1],
            ],
        ];
    }

    public static function invalidPayloads()
    {
        return [
            'handicap cannot be string' => [
                'payload' => [ 'handicap' => 'foo' ],
                'sessionError' => 'handicap'
            ],
            'handicap cannot be less than -54' => [
                'payload' => [ 'handicap' => -55 ],
                'sessionError' => 'handicap'
            ],
            'handicap cannot be greater than 54' => [
                'payload' => [ 'handicap' => 55 ],
                'sessionError' => 'handicap'
            ],
            'handicap cannot have more than one decimal' => [
                'payload' => [ 'handicap' => 55.55 ],
                'sessionError' => 'handicap'
            ]
        ];
    }
}
