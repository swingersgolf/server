<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\PreferenceUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferenceUserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test show method.
     *
     * @return void
     */
    public function test_show()
    {
        // Create a user and associated preferences
        $user = User::factory()->create();
        $preferences = PreferenceUser::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('api.v1.preference-user.show'))
            ->assertOk();
    }
}
