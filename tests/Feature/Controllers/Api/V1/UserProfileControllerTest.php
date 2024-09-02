<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserProfileControllerTest extends TestCase
{
    public function test_it_returns_user_profile(): void
    {
        $user = User::factory()->create();
        $handicap = 5;
        $user->userProfile->handicap = $handicap;
        $postalCode = 'H0H0H0';
        $user->userProfile->postal_code = $postalCode;
        $user->save();

        $response = $this->actingAs($user)->get(route('api.v1.user-profile.show'))
            ->assertOk();

        $responseData = $response->json('data');
        $this->assertEquals($handicap, $responseData['handicap']);
        $this->assertEquals($postalCode, $responseData['postalCode']);
    }
    #[DataProvider('validPayloads')]
    public function test_it_updates_user_profile_payloads($payload, $expectation): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('api.v1.user-profile.update'), $payload)
            ->assertSuccessful();

        $this->assertDatabaseHas('user_profiles', $expectation);
    }

    public static function validPayloads():array
    {
        return [
            'handicap' => [
                'payload' => ['handicap' => '12'],
                'expectation' => ['handicap' => '12'],
            ],
            'postalCode' => [
                'payload' => ['postalCode' => 'H0H0H0'],
                'expectation' => ['postal_code' => 'H0H0H0'],
            ]
        ];
    }
}
