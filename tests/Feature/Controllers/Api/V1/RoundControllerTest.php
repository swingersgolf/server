<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Course;
use App\Models\MessageGroup;
use App\Models\Preference;
use App\Models\Round;
use App\Models\User;
use App\Services\ProfilePhotoServiceInterface;
use Illuminate\Support\Carbon;
use Mockery;
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
            'course_name' => 'Some Course Name',
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

        $courseName = Course::find($round->course_id)->course_name;
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

    public function test_show_returns_round_with_preferences_and_message_group_id(): void
    {
        $mockProfilePhotoService = Mockery::mock(ProfilePhotoServiceInterface::class);
        $mockProfilePhotoService->shouldReceive('getPresignedUrl')
            ->times(3)
            ->andReturn('https://picsum.photos/200/300');
        $this->app->instance(ProfilePhotoServiceInterface::class, $mockProfilePhotoService);

        $users = User::factory()->count(3)->create();
        $where = Course::factory()->create([
            'course_name' => 'Some Course Name',
        ]);
        $preferences = Preference::factory()->count(3)->create();

        $messageGroup = MessageGroup::factory()->create([]);

        $round = Round::factory()->create([
            'date' => now()->format('Y-m-d'),
            'time_range' => 'morning',
            'course_id' => $where->id,
            'group_size' => 4,
            'message_group_id' => $messageGroup->id,
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
        $this->assertEquals($messageGroup->id, $responseData['message_group_id']);

        $courseName = Course::find($round->course_id)->course_name;
        $this->assertEquals($courseName, $responseData['course']);

        $this->assertCount($users->count(), $responseData['golfers']);
        $firstnames = $users->pluck('firstname')->toArray();
        array_map(function ($golfer) use ($firstnames) {
            $this->assertTrue(in_array($golfer['firstname'], $firstnames));
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

    public function test_accept_user_request_adds_user_to_rounds_message_group(): void
    {
        $user = User::factory()->create();
        $messageGroup = MessageGroup::factory()->create();
        $round = Round::factory()->create([
            'message_group_id' => $messageGroup->id,
        ]);
        $round->users()->attach($user->id, ['status' => 'pending']);

        $this->actingAs($user)->post(route('api.v1.round.accept', [
            'round' => $round->id,
            'user_id' => $user->id,
        ]))->assertSuccessful();

        $this->assertDatabaseHas('message_group_user', [
            'message_group_id' => $messageGroup->id,
            'user_id' => $user->id,
            'active' => true,
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
            'date' => '2021-10-10',
            'time_range' => 'morning',
            'course_id' => $course->id,
            'group_size' => 4,
            'preferences' => [
                $preference->id => 'preferred',  // Add a valid preference with status
            ],
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('rounds', [
            'date' => '2021-10-10',
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
        // Ensure the round's message group has been created
        $this->assertDatabaseHas('message_groups', [
            'id' => $response->json(['data'])['message_group_id'],
        ]);
        // Ensure the round host is added as a member of the messaging group
        $this->assertDatabaseHas('message_group_user', [
            'message_group_id' => $response->json(['data'])['message_group_id'],
            'user_id' => $user->id,
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
            'date' => '2021-10-10',
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
            'date' => '2021-10-10',
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

    public function test_delete_round_sets_message_group_to_inactive(): void
    {
        $user = User::factory()->create();
        $messageGroup = MessageGroup::factory()->create([
            'active' => true,
        ]);
        $round = Round::factory()->create([
            'message_group_id' => $messageGroup->id,
        ]);

        $response = $this->actingAs($user)->delete(route('api.v1.round.destroy', $round->id));

        $response->assertSuccessful();
        $this->assertDatabaseHas('message_groups', [
            'id' => $messageGroup->id,
            'active' => false,
        ]);
    }

    public function test_removeUser(): void
    {
        $host = User::factory()->create();

        $round = Round::factory()->create();
        $round->users()->attach($host->id);
        $round->host_id = $host->id;

        $roundMember = User::factory()->create();
        $round->users()->attach($roundMember->id);
        $round->save();

        $this->assertDatabaseHas('round_user', [
            'round_id' => $round->id,
            'user_id' => $roundMember->id,
        ]);

         $this->actingAs($host)->delete(route('api.v1.round-user.remove-user', $round->id), [
            'user_id' => $roundMember->id,
        ])->assertSuccessful();

        $this->assertDatabaseMissing('round_user', [
            'round_id' => $round->id,
            'user_id' => $roundMember->id,
        ]);
    }

    public function test_leave(): void
    {
        $round = Round::factory()->create();
        $roundMember = User::factory()->create();
        $round->users()->attach($roundMember->id);
        $round->save();

        $this->assertDatabaseHas('round_user', [
            'round_id' => $round->id,
            'user_id' => $roundMember->id,
        ]);

         $this->actingAs($roundMember)->delete(route('api.v1.round.leave', $round->id), [
            'user_id' => $roundMember->id,
        ])->assertSuccessful();

        $this->assertDatabaseMissing('round_user', [
            'round_id' => $round->id,
            'user_id' => $roundMember->id,
        ]);
    }
    public function test_leave_sets_message_group_user_to_inactive(): void
    {
        $messageGroup = MessageGroup::factory()->create([]);
        $round = Round::factory()->create([
            'message_group_id' => $messageGroup->id,
        ]);

        $roundMember = User::factory()->create();
        $round->users()->attach($roundMember->id);
        $round->save();

        $messageGroup->users()->attach($roundMember->id, ['active'=>true]);

        $this->assertDatabaseHas('message_group_user', [
            'message_group_id' => $messageGroup->id,
            'user_id' => $roundMember->id,
            'active' => true,
        ]);

         $this->actingAs($roundMember)->delete(route('api.v1.round.leave', $round->id), [
            'user_id' => $roundMember->id,
        ])->assertSuccessful();

        $this->assertDatabaseHas('message_group_user', [
            'message_group_id' => $messageGroup->id,
            'user_id' => $roundMember->id,
            'active' => false,
        ]);
    }
    public function test_removeUser_sets_message_group_user_inactive(): void
    {
        $host = User::factory()->create();

        $messageGroup = MessageGroup::factory()->create([]);
        $round = Round::factory()->create([
            'message_group_id' => $messageGroup->id,
        ]);
        $round->users()->attach($host->id);
        $round->host_id = $host->id;

        $roundMember = User::factory()->create();
        $round->users()->attach($roundMember->id);
        $round->save();

        $messageGroup->users()->attach([$host->id, $roundMember->id]);

        $this->assertDatabaseHas('message_group_user', [
            'message_group_id' => $messageGroup->id,
            'user_id' => $roundMember->id,
            'active' => true,
        ]);

         $this->actingAs($host)->delete(route('api.v1.round-user.remove-user', $round->id), [
            'user_id' => $roundMember->id,
        ])->assertSuccessful();

        $this->assertDatabaseHas('message_group_user', [
            'message_group_id' => $messageGroup->id,
            'user_id' => $roundMember->id,
            'active' => false,
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
