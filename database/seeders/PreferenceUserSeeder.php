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
            // Generate a random number of PreferenceUser entries between 1 and the count of preferences
            $numberOfPreferences = rand(1, $preferences->count());

            // Pick a random set of unique preferences for this user
            $userPreferences = $preferences->random($numberOfPreferences);

            // Create a PreferenceUser entry for each preference assigned to the user
            foreach ($userPreferences as $preference) {
                PreferenceUser::create([
                    'user_id' => $user->id,
                    'preference_id' => $preference->id,  // Ensure unique preference_id
                    'status' => fake()->randomElement(['indifferent', 'disliked', 'preferred']),
                ]);
            }
        }
    }
}
