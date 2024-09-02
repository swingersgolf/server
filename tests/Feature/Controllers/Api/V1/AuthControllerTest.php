<?php

namespace Tests\Feature\Controllers\Api\V1;

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

        $this->post(route('api.v1.login'), [
            'email' => $user->email,
            'password' => $password,
        ])->assertStatus(200);
    }

    public function test_it_validates_the_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->post(route('api.v1.login'), [
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
            'birth_date' => '1970-12-31',
        ];

        $this->post(route('api.v1.register'), $userPayload)
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

        $this->post(route('api.v1.register'), $userPayload)
            ->assertSessionHasErrors('email');
    }

    #[DataProvider('loginPayloads')]
    public function test_login_validates_payload($payload, $error): void
    {

        $this->post(route('api.v1.login'), $payload)
            ->assertSessionHasErrors($error);
    }

    #[DataProvider('registrationPayloads')]
    public function test_registration_validates_payload($payload, $error): void
    {

        $response = $this->post(route('api.v1.register'), $payload)
            ->assertSessionHasErrors($error);
    }

    public static function registrationPayloads(): array
    {
        return [
            'invalid date format m-d-Y' => [
                'payload' => [
                    'email' => 'birth_date@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthDate' => '12-31-1970',
                ],
                'error' => 'birth_date',
            ],
            'not 18' => [
                'payload' => [
                    'email' => 'birth_date@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthDate' => now()->subYears(17)->format('Y-m-d'),
                ],
                'error' => 'birth_date',
            ],
            'invalid date format m-d-y' => [
                'payload' => [
                    'email' => 'birthDate@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthDate' => '12-31-70',
                ],
                'error' => 'birth_date',
            ],
            'invalid date format Y-d-m' => [
                'payload' => [
                    'email' => 'birthDate@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthDate' => '1970-31-12',
                ],
                'error' => 'birth_date',
            ],
            'invalid date format - not a date' => [
                'payload' => [
                    'email' => 'birthDate@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthDate' => 'not a birthDate',
                ],
                'error' => 'birth_date',
            ],
            'birth date missing' => [
                'payload' => [
                    'email' => 'birthDate@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                ],
                'error' => 'birth_date',
            ],
            'name missing' => [
                'payload' => [
                    'email' => 'my.name@example.com',
                    'password' => 'password',
                    'birthDate' => '1970-01-31',
                ],
                'error' => 'name',
            ],
            'email missing' => [
                'payload' => [
                    'name' => 'my name',
                    'password' => 'password',
                    'birthDate' => '1970-01-31',
                ],
                'error' => 'email',
            ],
            'password missing' => [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'my.name@example.com',
                    'birthDate' => '1970-01-31',
                ],
                'error' => 'password',
            ],
            'password too short' => [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'my.name@example.com',
                    'password' => 'passwor',
                    'birthDate' => '1970-01-31',
                ],
                'error' => 'password',
            ],
            'invalid email format' => [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'not an email',
                    'password' => 'password',
                    'birthDate' => '1970-01-31',
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
