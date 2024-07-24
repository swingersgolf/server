<?php

namespace Tests\Feature\Api\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_it_logs_in_a_user(): void
    {
        $password = 'password';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $this->post('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ])->assertStatus(200);
    }

    public function test_it_validates_the_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'not password',
        ])->assertUnauthorized();
    }
    public function test_it_registers_a_new_user(): void
    {
        $userPayload = [
            'name' => 'my name',
            'email' => 'my.name@example.com',
            'password' => 'password',
        ];

        $this->post('/api/register', $userPayload)
            ->assertCreated();

        unset($userPayload['password']);

        $this->assertDatabaseHas('users', $userPayload);
    }

    #[DataProvider('loginPayloads')] public function test_login_validates_payload($payload, $error):void
    {

        $this->post('/api/login', $payload)
            ->assertSessionHasErrors($error);
    }

    #[DataProvider('registrationPayloads')] public function test_registration_validates_payload($payload, $error):void
    {

        $response = $this->post('/api/register', $payload)
            ->assertSessionHasErrors($error);
    }

    public static function registrationPayloads()
    {
        return[
            [
                'payload' => [
                    'email' => 'my.name@example.com',
                    'password' => 'password',
                ],
                'error' => 'name',
            ],
            [
                'payload' => [
                    'name' => 'my name',
                    'password' => 'password',
                ],
                'error' => 'email',
            ],
            [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'my.name@example.com',
                ],
                'error' => 'password',
            ],
            [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'my.name@example.com',
                    'password' => 'passwor',
                ],
                'error' => 'password',
            ],
            [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'not an email',
                    'password' => 'password',
                ],
                'error' => 'email',
            ]
        ];
    }
    public static function loginPayloads()
    {
        return[
            [
                'payload' => [
                    'password' => 'password',
                ],
                'error' => 'email',
            ],
            [
                'payload' => [
                    'email' => 'my.name@example.com',
                ],
                'error' => 'password',
            ],
            [
                'payload' => [
                    'email' => 'my.name@example.com',
                    'password' => 'passwor',
                ],
                'error' => 'password',
            ],
            [
                'payload' => [
                    'email' => 'not an email',
                    'password' => 'password',
                ],
                'error' => 'email',
            ]
        ];
    }

}
