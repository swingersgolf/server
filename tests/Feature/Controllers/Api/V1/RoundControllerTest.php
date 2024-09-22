<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Round;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoundControllerTest extends TestCase
{

    public function test_it_returns_all_rounds(): void
    {
        $user = User::factory()->create();
        Round::factory()->count(3)->create();
        $response = $this->actingAs($user)->get(route('api.v1.round.index'))
            ->assertSuccessful();
        $response->assertJsonCount(3);

    }
}
