<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Events\MessageEvent;
use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\Round;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    public function test_it_posts_message_and_returns_message(): void
    {
        Event::fake();
        $user = User::factory()->create();
        $message = 'Hello World';
        $messageGroup = MessageGroup::factory()->create();
        $messageGroup->users()->attach($user->id);
        $round = Round::factory()->create([
            'host_id' => $user->id,
            'message_group_id' => $messageGroup->id,
        ]);

        $payload = [
            'message_group_id' => $messageGroup->id,
            'message' => $message,
        ];

        $response = $this->actingAs($user)->postJson(
            route('api.v1.message.store'), $payload);

        $this->assertDatabaseHas('messages', [
            'user_id' => $user->id,
            'message_group_id' => $round->message_group_id,
            'message' => $message,
        ]);

        $this->assertEquals($payload['message'], $response->json('message'));
        $this->assertEquals($user->id, $response->json('user_id'));
        $this->assertEquals($messageGroup->id, $response->json('message_group_id'));

        Event::assertDispatched(MessageEvent::class, function ($event) use ($user, $messageGroup, $message) {
            $decodedEvent = json_decode($event->message, true);
            return $decodedEvent['user_id'] === $user->id &&
                $decodedEvent['message_group_id'] === $messageGroup->id &&
                $decodedEvent['message'] === $message;
        });
    }

    public function test_it_must_post_to_an_existing_message_group(): void
    {
        $user = User::factory()->create();
        $message = 'Hello World';
        $messageGroup = MessageGroup::factory()->create([
            'id' => 5,
        ]);
        Round::factory()->create([
            'host_id' => $user->id,
            'message_group_id' => $messageGroup->id,
        ]);

        $payload = [
            'message' => $message,
        ];

        $this->actingAs($user)->postJson(
            route('api.v1.message.store', [
                'message_group_id' => 55,
            ]), $payload)->assertStatus(422);

    }

    public function test_returns_403_if_the_user_does_not_belong_to_the_message_group(): void
    {
        $user = User::factory()->create();
        $message = 'Hello World';
        $messageGroup = MessageGroup::factory()->create([
            'id' => 5,
        ]);
        $otherMessageGroup = MessageGroup::factory()->create([
            'id' => 6,
        ]);
        $messageGroup->users()->attach($user);
        $round = Round::factory()->create([
            'host_id' => $user->id,
            'message_group_id' => $messageGroup->id,
        ]);

        $payload = [
            'message' => $message,
            'message_group_id' => $otherMessageGroup->id,
        ];

        $response = $this->actingAs($user)->postJson(
            route('api.v1.message.store'), $payload);

        $response->assertStatus(403);
    }

    public function test_post_returns_403_if_the_user_is_not_active_member_of_message_group(): void
    {
        $user = User::factory()->create();
        $message = 'Hello World';
        $messageGroup = MessageGroup::factory()->create([
            'id' => 5,
        ]);

        $messageGroup->users()->attach($user, ['active' => false]);
        $round = Round::factory()->create([
            'host_id' => $user->id,
            'message_group_id' => $messageGroup->id,
        ]);

        $payload = [
            'message' => $message,
            'message_group_id' => $messageGroup->id,
        ];

        $response = $this->actingAs($user)->postJson(
            route('api.v1.message.store'), $payload);

        $response->assertStatus(403);
    }

    public function test_post_returns_403_if_the_message_group_is_not_active(): void
    {
        $user = User::factory()->create();
        $message = 'Hello World';
        $messageGroup = MessageGroup::factory()->create([
            'id' => 5,
            'active' => false,
        ]);

        $messageGroup->users()->attach($user);
        $round = Round::factory()->create([
            'host_id' => $user->id,
            'message_group_id' => $messageGroup->id,
        ]);

        $payload = [
            'message' => $message,
            'message_group_id' => $messageGroup->id,
        ];

        $response = $this->actingAs($user)->postJson(
            route('api.v1.message.store'), $payload);

        $response->assertStatus(403);
    }

    public function test_index_returns_all_messages_for_message_group(): void
    {
        $users = User::factory()->count(2)->create();
        $messageGroup = MessageGroup::factory()->create([]);
        $messageGroup->users()->attach($users->pluck('id'));

        $anotherMessageGroup = MessageGroup::factory()->create([]);
        $anotherMessage = Message::factory()->create([
            'user_id' => $users[0]->id,
            'message_group_id' => $anotherMessageGroup->id,
            'message' => $users[0]->name,
        ]);

        $users->each(function ($user) use ($messageGroup) {
            Message::factory()->create([
                'message_group_id' => $messageGroup->id,
                'user_id' => $user->id,
                'message' => $user->name,
            ]);
        });

        $response = $this->actingAs($users[0])->getJson(route('api.v1.message.index', [
            'message_group_id' => $messageGroup->id,
        ]))
            ->assertSuccessful();
        $this->assertCount($users->count(), $response->original);
    }

    public function test_index_returns_json_error_if_group_does_not_exist(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('api.v1.message.index', [
            'message_group_id' => 5,
        ]))
            ->assertJsonValidationErrors('message_group_id');
    }

    public function test_index_returns_403_if_requester_not_member_of_message_group(): void
    {
        $users = User::factory()->count(2)->create();
        $messageGroup = MessageGroup::factory()->create([]);

        $users->each(function ($user) use ($messageGroup) {
            Message::factory()->create([
                'message_group_id' => $messageGroup->id,
                'user_id' => $user->id,
                'message' => $user->name,
            ]);
        });

        $response = $this->actingAs($users[0])->getJson(route('api.v1.message.index', [
            'message_group_id' => $messageGroup->id,
        ]));
        $response->assertStatus(403);
    }
    public function test_index_returns_403_if_requester_inactive_member_of_message_group(): void
    {
        $users = User::factory()->count(2)->create();
        $messageGroup = MessageGroup::factory()->create([]);
        $messageGroup->users()->attach($users->pluck('id'));
        $messageGroup->users()->updateExistingPivot($users[0]->id, ['active' => false]);

        $users->each(function ($user) use ($messageGroup) {
            Message::factory()->create([
                'message_group_id' => $messageGroup->id,
                'user_id' => $user->id,
                'message' => $user->name,
            ]);
        });

        $response = $this->actingAs($users[0])->getJson(route('api.v1.message.index', [
            'message_group_id' => $messageGroup->id,
        ]));
        $response->assertStatus(403);
    }
    public function test_index_returns_403_if_message_group_not_active(): void
    {
        $users = User::factory()->count(2)->create();
        $messageGroup = MessageGroup::factory()->create([
            'active' => false,
        ]);
        $messageGroup->users()->attach($users->pluck('id'));

        $users->each(function ($user) use ($messageGroup) {
            Message::factory()->create([
                'message_group_id' => $messageGroup->id,
                'user_id' => $user->id,
                'message' => $user->name,
            ]);
        });

        $response = $this->actingAs($users[0])->getJson(route('api.v1.message.index', [
            'message_group_id' => $messageGroup->id,
        ]));
        $response->assertStatus(403);
    }
}
