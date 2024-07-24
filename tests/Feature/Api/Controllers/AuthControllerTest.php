<?php

namespace Tests\Feature\Api\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_it_registers_a_new_user(): void
    {
        $userPayload = [
            'name' => 'my name',
            'email' => 'my.name@example.com',
            'password' => 'password',
        ];

        $response = $this->post('/api/register', $userPayload)
            ->assertCreated();

        unset($userPayload['password']);

        $this->assertDatabaseHas('users', $userPayload);
    }
}
