<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('payloads')] public function test_session_has_errors_when_payload_invalid($payload, $sessionError): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('api.v1.user-profile.update'), $payload)
            ->assertSessionHasErrors($sessionError);
    }

    public static function payloads()
    {
        return [
            'handicap cannot be string' => [
                'payload' => [ 'handicap' => 'foo' ],
                'sessionError' => 'handicap'
            ],
            'handicap cannot be less than -100' => [
                'payload' => [ 'handicap' => -101 ],
                'sessionError' => 'handicap'
            ],
            'handicap cannot be greater than 100' => [
                'payload' => [ 'handicap' => 101 ],
                'sessionError' => 'handicap'
            ]
        ];
    }
}
