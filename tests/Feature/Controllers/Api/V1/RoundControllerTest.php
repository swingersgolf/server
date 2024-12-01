<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Course;
use App\Models\Preference;
use App\Models\Round;
use App\Models\User;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoundControllerTest extends TestCase
{
    public function test_index_returns_all_rounds(): void
    {
        $user = User::factory()->create();
        $rounds = Round::factory()->count(3)->create();
        $response = $this->actingAs($user)->get(route('api.v1.round.index'))
            ->assertSuccessful();
        $this->assertEquals($rounds->count(), count($response->json()['data']));
    }

    public function test_index_returns_rounds_with_preferences(): void
    {
        $users = User::factory()->count(3)->create();
        $where = Course::factory()->create([
            'name' => 'Some Course Name',
        ]);
        $preferences = Preference::factory()->count(3)->create();

        $round = Round::factory()->create([
            'date' => now(),
            'time_range' => 'morning',
            'course_id' => $where->id,
            'group_size' => 4,
        ]);

        $round->preferences()->attach($preferences[0], [
            'status' => Preference::STATUS_PREFERRED,
        ]);
        $round->preferences()->attach($preferences[1], [
            'status' => Preference::STATUS_DISLIKED,
        ]);
        $round->preferences()->attach($preferences[2], [
            'status' => Preference::STATUS_INDIFFERENT,
        ]);

        $round->users()->attach($users->pluck('id'), ['status' => 'accepted']);

        $response = $this->actingAs($users[0])->get(route('api.v1.round.index'))
            ->assertSuccessful();
        $responseData = $response->json()['data'][0];

        // Format the round date to 'Y-m-d' before comparing
        $formattedRoundDate = $round->date->format('Y-m-d');
        $formattedResponseDate = Carbon::parse($responseData['date'])->format('Y-m-d');

        // Compare the dates only (ignore time)
        $this->assertEquals($formattedRoundDate, $formattedResponseDate);

        $courseName = Course::find($round->course_id)->name;
        $this->assertEquals($courseName, $responseData['course']);
        $this->assertEquals($users->count(), $responseData['golfer_count']);
        $this->assertEquals($round->group_size, $responseData['group_size']);

        $this->assertEquals($preferences[0]->name, $responseData['preferences'][0]['name']);
        $this->assertEquals($preferences[0]->id, $responseData['preferences'][0]['id']);
        $this->assertEquals('preferred', $responseData['preferences'][0]['status']);
        $this->assertEquals($preferences[1]->name, $responseData['preferences'][1]['name']);
        $this->assertEquals($preferences[1]->id, $responseData['preferences'][1]['id']);
        $this->assertEquals('disliked', $responseData['preferences'][1]['status']);
        $this->assertEquals($preferences[2]->name, $responseData['preferences'][2]['name']);
        $this->assertEquals($preferences[2]->id, $responseData['preferences'][2]['id']);
        $this->assertEquals(Preference::STATUS_INDIFFERENT, $responseData['preferences'][2]['status']);
    }

    #[DataProvider('dateScenarios')]
    public function test_index_returns_rounds_filtered_by_dates($start, $end, $count): void
    {
        Carbon::setTestNow(now());
        $user = User::factory()->create();
        Round::factory()->create([
            'date' => now()->addDays(2)->format('Y-m-d H:i'),
        ]);
        Round::factory()->create([
            'date' => now()->addDays(4)->format('Y-m-d H:i'),
        ]);
        $response = $this->actingAs($user)->getJson(
            route('api.v1.round.index', [
                'start' => now()->addDays($start)->format('Y-m-d H:i'),
                'end' => now()->addDays($end)->format('Y-m-d H:i'),
            ]))
            ->assertSuccessful();
        $this->assertEquals($count, count($response->json()['data']));
    }

    public function test_show_returns_round_with_preferences(): void
    {
        $users = User::factory()->count(3)->create();
        $where = Course::factory()->create([
            'name' => 'Some Course Name',
        ]);
        $preferences = Preference::factory()->count(3)->create();

        $round = Round::factory()->create([
            'date' => now()->format('Y-m-d'),
            'time_range' => 'morning',
            'course_id' => $where->id,
            'group_size' => 4,
        ]);

        $round->preferences()->attach($preferences[0], [
            'status' => Preference::STATUS_PREFERRED,
        ]);
        $round->preferences()->attach($preferences[1], [
            'status' => Preference::STATUS_DISLIKED,
        ]);
        $round->preferences()->attach($preferences[2], [
            'status' => Preference::STATUS_INDIFFERENT,
        ]);

        $round->users()->attach($users);

        $response = $this->actingAs($users[0])->get(route('api.v1.round.show', $round->id))
            ->assertSuccessful();
        $responseData = $response->json()['data'];

        $this->assertEquals($round->date, $responseData['date']);

        $courseName = Course::find($round->course_id)->name;
        $this->assertEquals($courseName, $responseData['course']);

        $this->assertCount($users->count(), $responseData['golfers']);
        $names = $users->pluck('name')->toArray();
        array_map(function ($golfer) use ($names) {
            $this->assertTrue(in_array($golfer['name'], $names));
        }, $responseData['golfers']);

        $this->assertEquals($preferences[0]->name, $responseData['preferences'][0]['name']);
        $this->assertEquals($preferences[0]->id, $responseData['preferences'][0]['id']);
        $this->assertEquals('preferred', $responseData['preferences'][0]['status']);
        $this->assertEquals($preferences[1]->name, $responseData['preferences'][1]['name']);
        $this->assertEquals($preferences[1]->id, $responseData['preferences'][1]['id']);
        $this->assertEquals('disliked', $responseData['preferences'][1]['status']);
        $this->assertEquals($preferences[2]->name, $responseData['preferences'][2]['name']);
        $this->assertEquals($preferences[2]->id, $responseData['preferences'][2]['id']);
        $this->assertEquals(Preference::STATUS_INDIFFERENT, $responseData['preferences'][2]['status']);
    }

    public function test_join_submits_request(): void
    {
        $user = User::factory()->withExpoPushToken()->create();
        $round = Round::factory()->create();

        $response = $this->actingAs($user)->post(route('api.v1.round.join', $round->id));

        $response->assertSuccessful();
        $this->assertDatabaseHas('round_user', [
            'round_id' => $round->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_accept_user_request(): void
    {
        $user = User::factory()->withExpoPushToken()->create();
        $round = Round::factory()->create();
        $round->users()->attach($user->id, ['status' => 'pending']);

        $response = $this->actingAs($user)->post(route('api.v1.round.accept', [
            'round' => $round->id,
            'user_id' => $user->id,
        ]));

        $response->assertSuccessful();
        $this->assertDatabaseHas('round_user', [
            'round_id' => $round->id,
            'user_id' => $user->id,
            'status' => 'accepted',
        ]);
    }

    public function test_reject_user_request(): void
    {
        $user = User::factory()->withExpoPushToken()->create();
        $round = Round::factory()->create();
        $round->users()->attach($user->id, ['status' => 'pending']);

        $response = $this->actingAs($user)->post(route('api.v1.round.reject', [
            'round' => $round->id,
            'user_id' => $user->id,
        ]));

        $response->assertSuccessful();
        $this->assertDatabaseHas('round_user', [
            'round_id' => $round->id,
            'user_id' => $user->id,
            'status' => 'rejected',
        ]);
    }

    public function test_create_round(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        // Create a preference to use in the test
        $preference = Preference::factory()->create();
    
        $response = $this->actingAs($user)->post(route('api.v1.round.store'), [
            'date' => "2021-10-10",
            'time_range' => 'morning',
            'course_id' => $course->id,
            'group_size' => 4,
            'preferences' => [
                $preference->id => 'preferred',  // Add a valid preference with status
            ],
        ]);
    
        $response->assertSuccessful();
        $this->assertDatabaseHas('rounds', [
            'date' => "2021-10-10",
            'time_range' => 'morning',
            'course_id' => $course->id,
            'group_size' => 4,
        ]);
        // Ensure that the preference was attached correctly
        $this->assertDatabaseHas('preference_round', [
            'round_id' => Round::first()->id,
            'preference_id' => $preference->id,
            'status' => 'preferred', // Ensure the status is set properly
        ]);
    }
    
    public function test_update_round(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $round = Round::factory()->create();
        // Create a preference to use in the test
        $preference = Preference::factory()->create();
    
        $response = $this->actingAs($user)->patch(route('api.v1.round.update', $round->id), [
            'date' => "2021-10-10",
            'time_range' => 'morning',
            'course_id' => $course->id,
            'group_size' => 4,
            'preferences' => [
                $preference->id => 'preferred',  // Add a valid preference with status
            ],
        ]);
    
        $response->assertSuccessful();
        $this->assertDatabaseHas('rounds', [
            'id' => $round->id,
            'date' => "2021-10-10",
            'time_range' => 'morning',
            'course_id' => $course->id,
            'group_size' => 4,
        ]);
        // Ensure that the preference was updated correctly
        $this->assertDatabaseHas('preference_round', [
            'round_id' => $round->id,
            'preference_id' => $preference->id,
            'status' => 'preferred', // Ensure the status is set properly
        ]);
    }
    
    public function test_delete_round(): void
    {
        $user = User::factory()->create();
        $round = Round::factory()->create();

        $response = $this->actingAs($user)->delete(route('api.v1.round.destroy', $round->id));

        $response->assertSuccessful();
        $this->assertDatabaseMissing('rounds', [
            'id' => $round->id,
        ]);
    }

    public static function dateScenarios(): array
    {
        return [
            'all rounds' => [
                'start' => 1,
                'end' => 5,
                'count' => 2,
            ],
            'second round only' => [
                'start' => 3,
                'end' => 5,
                'count' => 1,
            ],
            'first round only' => [
                'start' => 0,
                'end' => 3,
                'count' => 1,
            ],
            'out of range' => [
                'start' => 6,
                'end' => 9,
                'count' => 0,
            ],
        ];
    }
}
