<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_login_logs_in_a_user(): void
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

    public function test_login_validates_the_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->post(route('api.v1.login'), [
            'email' => $user->email,
            'password' => 'not password',
        ])->assertUnauthorized();
    }

    public function test_login_rejects_login_without_verified_email(): void
    {
        $password = 'password';
        $user = User::factory()->create([
            'password' => Hash::make($password),
            'email_verified_at' => null,
        ]);

        $this->post(route('api.v1.login'), [
            'email' => $user->email,
            'password' => $password,
        ])->assertStatus(ResponseAlias::HTTP_PRECONDITION_REQUIRED);
    }

    #[DataProvider('loginPayloads')]
    public function test_login_validates_payload($payload, $error): void
    {

        $this->post(route('api.v1.login'), $payload)
            ->assertSessionHasErrors($error);
    }

    public function test_register_registers_a_new_user(): void
    {
        $userPayload = [
            'name' => 'my name',
            'email' => 'my.name@example.com',
            'password' => 'password',
            'birthdate' => '1970-12-31',
        ];

        $this->post(route('api.v1.register'), $userPayload)
            ->assertCreated();

        unset($userPayload['password']);

        $this->assertDatabaseHas('users', $userPayload);
    }

    public function test_register_sends_email_verification_notification_and_caches_code(): void
    {
        Notification::fake();
        $email = 'my.name@example.com';
        $userPayload = [
            'name' => 'my name',
            'email' => $email,
            'password' => 'password',
            'birthdate' => '1970-12-31',
        ];

        $this->post(route('api.v1.register'), $userPayload)
            ->assertCreated();
        $user = User::first();
        Notification::assertSentTo($user, VerifyEmailNotification::class);
        Cache::shouldReceive('put')
            ->with('verification_code_'.$email);
    }

    public function test_registers_email_verification_contains_code_and_expiry(): void
    {
        Notification::fake();
        $user = User::factory()->create([]);
        $code = 123456;
        $expiry = 30;
        $user->notify(new VerifyEmailNotification($code, $expiry));
        Notification::assertSentTo($user, VerifyEmailNotification::class,
            function (VerifyEmailNotification $notification) use ($code, $expiry) {
                $mailContents = $notification->toMail($notification);

                return
                    $mailContents->actionUrl === strval($code) &&
                    $mailContents->outroLines[0] === 'This code will expire in '.$expiry.' minutes.';
            });
    }

    public function test_register_prevents_duplicate_user_registration(): void
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

    #[DataProvider('registrationPayloads')]
    public function test_register_validates_payload($payload, $error): void
    {

        $response = $this->post(route('api.v1.register'), $payload)
            ->assertSessionHasErrors($error);
    }

    public function test_verify_validates_the_code(): void
    {
        $email = 'foo@bar.com';
        $code = '123456';
        $expires_at = now()->addMinutes(30);

        $user = User::factory()->create([
            'email' => $email,
            'email_verified_at' => null,
        ]);

        Cache::put('verification_code_'.$email, [
            'code' => strval($code),
            'expires_at' => $expires_at,
        ], $expires_at);

        $response = $this->post(route('api.v1.verify'), ['email' => $email, 'code' => $code]);
        $response->assertStatus(200);

        Cache::flush();
    }

    public function test_verification_rejects_expired_code(): void
    {
        $email = 'foo@bar.com';
        $code = '123456';
        $expires_at = now()->subMinutes(5);

        $user = User::factory()->create([
            'email' => $email,
            'email_verified_at' => null,
        ]);

        Cache::put('verification_code_'.$email, [
            'code' => strval($code),
            'expires_at' => $expires_at,
        ]);

        $response = $this->post(route('api.v1.verify'), ['email' => $email, 'code' => $code]);
        $response->assertStatus(ResponseAlias::HTTP_PRECONDITION_REQUIRED);

        Cache::flush();
    }

    public function test_verification_rejects_incorrect_code(): void
    {
        $email = 'foo@bar.com';
        $code = '123456';
        $expires_at = now()->addMinutes(30);

        $user = User::factory()->create([
            'email' => $email,
            'email_verified_at' => null,
        ]);

        Cache::put('verification_code_'.$email, [
            'code' => '567890',
            'expires_at' => $expires_at,
        ]);

        $response = $this->post(route('api.v1.verify'), ['email' => $email, 'code' => $code]);
        $response->assertStatus(ResponseAlias::HTTP_PRECONDITION_REQUIRED);

        Cache::flush();
    }

    public function test_verify_requires_email_and_code(): void
    {
        $this->post(route('api.v1.verify'))
            ->assertSessionHasErrors(['email', 'code']);
    }

    public function test_verify_requires_email_that_exists(): void
    {
        User::factory()->create([
            'email' => 'foo@bar.com',
            'email_verified_at' => null,
        ]);
        $this->post(route('api.v1.verify'),
            [
                'email' => 'not_foo@bar.com',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_resend_sends_email_verification_notification(): void
    {
        Notification::fake();

        $email = 'foo@bar.com';

        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password'),
            'birthdate' => '1970-12-31',
            'email_verified_at' => null,
        ]);

        $this->post(route('api.v1.resend'), [
            'email' => $email]
        );

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_requires_email(): void
    {
        $this->post(route('api.v1.verify'))
            ->assertSessionHasErrors(['email']);
    }

    public function test_resend_requires_email_that_exists(): void
    {
        User::factory()->create([
            'email' => 'foo@bar.com',
            'email_verified_at' => null,
        ]);

        $this->post(route('api.v1.verify'), [
            'email' => 'not_foo@bar.com',
        ])
            ->assertSessionHasErrors(['email']);
    }

    public static function registrationPayloads(): array
    {
        return [
            'invalid date format m-d-Y' => [
                'payload' => [
                    'email' => 'birthdate@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthdate' => '12-31-1970',
                ],
                'error' => 'birthdate',
            ],
            'not 18' => [
                'payload' => [
                    'email' => 'birthdate@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthdate' => now()->subYears(17)->format('Y-m-d'),
                ],
                'error' => 'birthdate',
            ],
            'invalid date format m-d-y' => [
                'payload' => [
                    'email' => 'birthdate@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthdate' => '12-31-70',
                ],
                'error' => 'birthdate',
            ],
            'invalid date format Y-d-m' => [
                'payload' => [
                    'email' => 'birthdate@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthdate' => '1970-31-12',
                ],
                'error' => 'birthdate',
            ],
            'invalid date format - not a date' => [
                'payload' => [
                    'email' => 'birthdate@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                    'birthdate' => 'not a birthdate',
                ],
                'error' => 'birthdate',
            ],
            'birth date missing' => [
                'payload' => [
                    'email' => 'birthdate@example.com',
                    'password' => 'password',
                    'name' => 'Birthday',
                ],
                'error' => 'birthdate',
            ],
            'name missing' => [
                'payload' => [
                    'email' => 'my.name@example.com',
                    'password' => 'password',
                    'birthdate' => '1970-01-31',
                ],
                'error' => 'name',
            ],
            'email missing' => [
                'payload' => [
                    'name' => 'my name',
                    'password' => 'password',
                    'birthdate' => '1970-01-31',
                ],
                'error' => 'email',
            ],
            'password missing' => [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'my.name@example.com',
                    'birthdate' => '1970-01-31',
                ],
                'error' => 'password',
            ],
            'password too short' => [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'my.name@example.com',
                    'password' => 'passwor',
                    'birthdate' => '1970-01-31',
                ],
                'error' => 'password',
            ],
            'invalid email format' => [
                'payload' => [
                    'name' => 'my name',
                    'email' => 'not an email',
                    'password' => 'password',
                    'birthdate' => '1970-01-31',
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
