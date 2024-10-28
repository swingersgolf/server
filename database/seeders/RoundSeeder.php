<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Course;
use App\Models\Preference;
use App\Models\Round;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RoundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rounds = Round::factory()->count(20)->create([
            'when' => fn () => Carbon::now()
                ->addMinutes(rand(0, 3 * 7 * 24 * 60))
                ->format('Y-m-d H:i'),
            'spots' => fn () => rand(2, 4),
        ]);

        $preferenceIds = Preference::pluck('id');
        $userIds = User::pluck('id');
        $courseIds = Course::pluck('id');

        $rounds->each(function ($round) use ($preferenceIds, $userIds, $courseIds) {
            $preferenceIds->each(function ($preferenceId) use ($round) {
                $option = rand(1, 3);
                switch ($option) {
                    case 1:
                        $round->preferences()->attach($preferenceId, [
                            'status' => Preference::STATUS_PREFERRED,
                        ]);
                        break;
                    case 2:
                        $round->preferences()->attach($preferenceId, [
                            'status' => Preference::STATUS_DISLIKED,
                        ]);
                        break;
                    case 3:
                        $round->preferences()->attach($preferenceId, [
                            'status' => Preference::STATUS_INDIFFERENT,
                        ]);
                }
            });

            // Determine the number of users for the round
            $numUsers = rand(1, $round->spots);

            // Get random users and sync them with an 'accepted' status
            $selectedUsers = $userIds->random($numUsers)->toArray();
            $round->users()->sync(array_map(function ($userId) {
                return [
                    'user_id' => $userId, // Include user_id here
                    'status' => 'accepted',  // Set all users to 'accepted'
                ];
            }, $selectedUsers));            

            // Set a random course ID
            $round->course_id = $courseIds->random();

            // Assign the host_id from the selected users
            $round->host_id = $selectedUsers[array_rand($selectedUsers)];

            // Save the round
            $round->save();
        });

    }
}
