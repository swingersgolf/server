<?php

namespace Tests\Feature;

use App\Models\Preference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index()
    {
        // Create a user to act as the authenticated user
        $user = User::factory()->create();

        // Create 3 preferences
        $preferences = Preference::factory()->count(3)->create();

        // Make a GET request to the preferences index route
        $response = $this->actingAs($user)->get(route('api.v1.preference.index'))
            ->assertOk();

        // Assert that the response status is OK (200)
        $response->assertStatus(200);

        // Assert that the response contains 3 preferences in the 'data' field
        $response->assertJsonCount(3, 'data');
    }
}
