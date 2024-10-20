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
            $numUsers = rand(1, $round->spots);
            $round->users()->sync(
                $userIds->random($numUsers)->toArray(),
            );

            $round->course_id = rand(1, $courseIds->count());
            $round->save();
        });

    }
}
