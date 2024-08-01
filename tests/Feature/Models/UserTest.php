<?php

namespace Tests\Feature\Models;

use App\Models\User;
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

    public function test_it_creates_empty_profile(): void
    {
        $user = User::factory()->create();
        $this->assertNull($user->userProfile->handicap);
    }
}
