<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserControllerTest extends TestCase
{

    public function test_it_returns_user_with_profile(): void
    {
        $name = 'John Doe';
        $email = 'john.doe@example.com';
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
        ]);

        $userProfile = UserProfile::firstWhere('user_id', $user->id);
        $handicap = 10.4;
        $userProfile->update(['handicap' => $handicap]);

        $response = $this->actingAs($user)->get(route('api.v1.user.show', $user));
        $responseData = $response->json('data');
        $this->assertEquals($name, $responseData['name']);
        $this->assertEquals($email, $responseData['email']);
        $this->assertEquals($handicap, $responseData['handicap']);
    }
    #[DataProvider('validPayloads')]
    public function test_it_updates_user_profile_handicap($payload): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('api.v1.user.update'), $payload)
            ->assertSuccessful();

        $this->assertDatabaseHas('user_profiles', $payload);
    }

    public function test_it_cannot_update_user_id(): void
    {
        $user = User::factory()->create();

        $payload = [
            'user_id' => Str::uuid(),
        ];

        $this->actingAs($user)->patch(route('api.v1.user.update'), $payload)
            ->assertSuccessful();

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
        ]);
    }

    #[DataProvider('invalidPayloads')]
    public function test_session_has_errors_when_payload_invalid($payload, $sessionError): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('api.v1.user.update'), $payload)
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
                'payload' => ['handicap' => 'foo'],
                'sessionError' => 'handicap',
            ],
            'handicap cannot be less than -54' => [
                'payload' => ['handicap' => -55],
                'sessionError' => 'handicap',
            ],
            'handicap cannot be greater than 54' => [
                'payload' => ['handicap' => 55],
                'sessionError' => 'handicap',
            ],
            'handicap cannot have more than one decimal' => [
                'payload' => ['handicap' => 55.55],
                'sessionError' => 'handicap',
            ],
        ];
    }
}
