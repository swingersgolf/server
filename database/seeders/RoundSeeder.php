<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Course;
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
            'when' => fn() => Carbon::now()
                ->addMinutes(rand(0, 3 * 7 * 24 * 60))
                ->format('Y-m-d H:i'),
            'spots' => fn() => rand(2,4)
        ]);
        $attributeIds = Attribute::pluck('id');
        $userIds = User::pluck('id');
        $courseIds = Course::pluck('id');

        $rounds->each(function ($round) use ($attributeIds, $userIds, $courseIds) {
            $numAttributes = rand(0, $attributeIds->count());
            if ($numAttributes > 0) {
                $round->attributes()->attach(
                    $attributeIds->random($numAttributes)->toArray(),
                );
            }

            $numUsers = rand(1, $round->spots);
            $round->users()->sync(
                $userIds->random($numUsers)->toArray(),
            );

            $round->course_id = rand(1, $courseIds->count());
            $round->save();
        });

    }
}
