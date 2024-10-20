<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Preference;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function test_it_returns_user_with_preferences(): void
    {
        $name = 'John Doe';
        $email = 'john.doe@example.com';
        $birthdate = '2000-11-30';
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'birthdate' => $birthdate,
            'password' => Hash::make('password'),
        ]);

        $preference = Preference::factory()->count(3)->create();
        $preferenceNames = $preference->pluck('name');
        $user->preferences()->attach($preference, ['status'=>Preference::STATUS_PREFERRED]);

        $response = $this->actingAs($user)->get(route('api.v1.user.show'));
        $responseData = $response->json('data');
        $this->assertEquals($name, $responseData['name']);
        $this->assertEquals($email, $responseData['email']);
        $this->assertEquals($birthdate, $responseData['birthdate']);
        foreach ($responseData['preferences'] as $preference) {
            $this->assertContains($preference['name'], $preferenceNames);
        }
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

    #[DataProvider('invalidPayloads')]
    public function test_session_has_errors_when_payload_invalid($payload, $sessionError): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('api.v1.user-profile.update'), $payload)
            ->assertSessionHasErrors($sessionError);
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
