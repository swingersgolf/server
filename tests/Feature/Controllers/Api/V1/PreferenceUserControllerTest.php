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

    public function test_update()
    {
        $user = User::factory()->create();
        $preferences = PreferenceUser::factory()->count(3)->create(['user_id' => $user->id]);

        // Correct the typo here from 'preferance_name' to 'preference_name'
        $newPreferences = [
            [
                'preference_id' => $preferences[0]->preference_id,
                'status' => 'preferred',
            ],
            [
                'preference_id' => $preferences[1]->preference_id,
                'status' => 'indifferent',
            ],
        ];

        // Send the patch request
        $response = $this->actingAs($user)->patch(route('api.v1.preference-user.update'), [
            'preferences' => $newPreferences,
        ]);

        // Assert that the response is successful
        $response->assertOk();

        // Assert that the updated preferences are in the database
        foreach ($newPreferences as $newPreference) {
            $this->assertDatabaseHas('preference_user', [
                'user_id' => $user->id,
                'preference_id' => $newPreference['preference_id'],
                'status' => $newPreference['status'],
            ]);
        }

        // Assert the other preferences remain unchanged
        foreach ($preferences as $preference) {
            if (!in_array($preference->preference_id, array_column($newPreferences, 'preference_id'))) {
                $this->assertDatabaseHas('preference_user', [
                    'user_id' => $user->id,
                    'preference_id' => $preference->preference_id,
                    'status' => $preference->status,
                ]);
            }
        }
    }
}
