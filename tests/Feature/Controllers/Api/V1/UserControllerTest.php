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
