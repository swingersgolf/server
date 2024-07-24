<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_it_creates_with_uuid(): void
    {
        $uuid = Str::uuid();
        $user = User::factory()->create([
            'id' => $uuid,
        ]);
        $this->assertDatabaseHas('users', $user->toArray());
    }
}
