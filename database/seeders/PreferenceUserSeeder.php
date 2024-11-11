<?php

namespace Database\Seeders;

use App\Models\Preference;
use App\Models\PreferenceUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class PreferenceUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fetch all users
        $users = User::all();

        // Fetch all preferences (to choose from for each user)
        $preferences = Preference::all();

        // Iterate over each user
        foreach ($users as $user) {
            // Iterate over each preference and create a PreferenceUser entry for each one
            foreach ($preferences as $preference) {
                PreferenceUser::create([
                    'user_id' => $user->id,
                    'preference_id' => $preference->id,  // Ensure unique preference_id
                    'status' => fake()->randomElement(['indifferent', 'disliked', 'preferred']),
                ]);
            }
        }
    }
}
