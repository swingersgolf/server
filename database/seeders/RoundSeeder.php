<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Message;
use App\Models\MessageGroup;
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
        $preferenceIds = Preference::pluck('id');
        $courseIds = Course::pluck('id');

        // broadcast testing seeder
        $firstRound = Round::factory()->create([
            'date' => fn() => Carbon::now()
                ->addMinutes(rand(0, 3 * 7 * 24 * 60))  // Add random minutes to get a future date
                ->format('Y-m-d'),
            'time_range' => fn() => $this->getRandomTimeRange(),
            // Get a random time range (morning, afternoon, evening)
            'group_size' => 3,
            'message_group_id' => fn() => MessageGroup::factory()->create()->id,
        ]);
        $preferenceIds->each(function ($preferenceId) use ($firstRound) {
            $firstRound->preferences()->attach($preferenceId, [
                'status' => Preference::STATUS_PREFERRED,
            ]);
        });
        $userSender = User::where('email','sender@example.com')->first();
        $userListener = User::where('email','listener@example.com')->first();
        $firstRound->users()->attach($userSender->id, [
            'status'=>'accepted'
        ]);
        $firstRound->users()->attach($userListener->id, [
            'status'=>'accepted'
        ]);
        $firstRound->course_id = $courseIds->random();
        $firstRound->host_id = $userSender->id;
        $firstRound->messageGroup->users()->attach($userSender->id, ['active' => true]);
        $firstRound->messageGroup->users()->attach($userListener->id, ['active' => true]);
        $firstRound->save();

        // rest of round seeding
        $rounds = Round::factory()->count(19)->create([
            'date' => fn() => Carbon::now()
                ->addMinutes(rand(0, 3 * 7 * 24 * 60))  // Add random minutes to get a future date
                ->format('Y-m-d'),
            'time_range' => fn() => $this->getRandomTimeRange(),
            // Get a random time range (morning, afternoon, evening)
            'group_size' => fn() => rand(2, 4),
            'message_group_id' => fn() => MessageGroup::factory()->create()->id,
        ]);

        $userIds = User::pluck('id');

        $rounds->each(function ($round) use ($preferenceIds, $userIds, $courseIds) {
            // Assign preferences
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
                        break;
                }
            });

            // Determine the number of users for the round
            $numUsers = rand(1, $round->group_size);

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

            foreach ($selectedUsers as $userId) {
                $round->messageGroup->users()->attach($userId, ['active' => true]);
            }

            // Save the round
            $round->save();
        });
    }

    /**
     * Get a random time range for the round.
     *
     * @return string
     */
    private function getRandomTimeRange(): string
    {
        $timeRanges = [
            Round::TIME_RANGE_EARLY_BIRD,
            Round::TIME_RANGE_MORNING,
            Round::TIME_RANGE_AFTERNOON,
            Round::TIME_RANGE_TWILIGHT,
        ];

        return $timeRanges[array_rand($timeRanges)];
    }
}
