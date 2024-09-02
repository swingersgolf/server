<?php

namespace Tests\Feature\Controllers\Api;

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

        $this->post(route('api.login'), [
            'email' => $user->email,
            'password' => $password,
        ])->assertStatus(200);
    }

    public function test_it_validates_the_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->post(route('api.login'), [
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
            'birthday' => '1970-12-31',
        ];

        $this->post(route('api.register'), $userPayload)
            ->assertCreated();

        unset($userPayload['password']);

        $this->assertDatabaseHas('users', $userPayload);
    }

    public function test_it_prevents_duplicate_user_registration(): void
    {
        $userPayload = [
            'name' => 'my name',
            'email' => 'my.name@example.com',
            'password' => 'password',
        ];

        User::factory()->create([
            'name' => $userPayload['name'],
            'email' => $userPayload['email'],
            'password' => Hash::make('password'),
        ]);

        $this->post(route('api.register'), $userPayload)
            ->assertSessionHasErrors('email');
    }

    #[DataProvider('loginPayloads')]
    public function test_login_validates_payload($payload, $error): void
    {

        $this->post(route('api.login'), $payload)
            ->assertSessionHasErrors($error);
    }

    #[DataProvider('registrationPayloads')]
    public function test_registration_validates_payload($payload, $error): void
    {

        $response = $this->post('/api/register', $payload)
            ->assertSessionHasErrors($error);
    }

    public static function registrationPayloads()
    {
        return [
            [
                'payload' => [
                    'email' => 'birthday@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthday' => '12-31-1970',
                ],
                'error' => 'birthday',
            ],
            [
                'payload' => [
                    'email' => 'birthday@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthday' => '12-31-70',
                ],
                'error' => 'birthday',
            ],
            [
                'payload' => [
                    'email' => 'birthday@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthday' => '1970-31-12',
                ],
                'error' => 'birthday',
            ],
            [
                'payload' => [
                    'email' => 'birthday@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthday' => 'not a birthday',
                ],
                'error' => 'birthday',
            ],
            [
                'payload' => [
                    'email' => 'birthday@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                ],
                'error' => 'birthday',
            ],
            [
                'payload' => [
                    'email' => 'my.name@example.com',
                    'password' => 'password',
                    'birthday' => '1970-01-31',
                ],
                'error' => 'name',
            ],
            [
                'payload' => [
                    'name' => 'my name',
                    'password' => 'password',
                    'birthday' => '1970-01-31',
                ],
                'error' => 'email',
            ],
            [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'my.name@example.com',
                    'birthday' => '1970-01-31',
                ],
                'error' => 'password',
            ],
            [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'my.name@example.com',
                    'password' => 'passwor',
                    'birthday' => '1970-01-31',
                ],
                'error' => 'password',
            ],
            [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'not an email',
                    'password' => 'password',
                    'birthday' => '1970-01-31',
                ],
                'error' => 'email',
            ],
        ];
    }

    public static function loginPayloads()
    {
        return [
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
            ],
        ];
    }
}
