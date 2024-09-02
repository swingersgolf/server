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

    #[DataProvider('invalidPayloads')]
    public function test_it_returns_errors_for_invalid_user_profile_payloads($payload, $error): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('api.v1.user-profile.update'), $payload)
            ->assertSessionHasErrors($error);
    }

    public static function invalidPayloads():array
    {
        return [
            'handicap over' => [
                'payload' => ['handicap' => '55'],
                'error' => 'handicap'
            ],
            'handicap under' => [
                'payload' => ['handicap' => '-55'],
                'error' => 'handicap'
            ],
            'handicap not a number' => [
                'payload' => ['handicap' => 'foobar'],
                'error' => 'handicap'
            ],
            'invalid Canadian postal code' => [
                'payload' => ['postalCode' => 'ABCDEF'],
                'error' => 'postal_code'
            ],
            'invalid American zip code' => [
                'payload' => ['postalCode' => '987654'],
                'error' => 'postal_code'
            ],
            'invalid American long zip code' => [
                'payload' => ['postalCode' => '90210-12345'],
                'error' => 'postal_code'
            ],
        ];
    }

    public static function validPayloads():array
    {
        return [
            'handicap' => [
                'payload' => ['handicap' => '12'],
                'expectation' => ['handicap' => '12'],
            ],
            'Canadian postalCode' => [
                'payload' => ['postalCode' => 'H0H0H0'],
                'expectation' => ['postal_code' => 'H0H0H0'],
            ],
            'American zip' => [
                'payload' => ['postalCode' => '90210'],
                'expectation' => ['postal_code' => '90210'],
            ],
            'American long zip' => [
                'payload' => ['postalCode' => '90210-1234'],
                'expectation' => ['postal_code' => '90210-1234'],
            ]
        ];
    }
}
