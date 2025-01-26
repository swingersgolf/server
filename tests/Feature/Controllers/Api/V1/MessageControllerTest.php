<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\Round;
use App\Models\User;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    public function test_it_posts_message_and_returns_message(): void
    {
        $user = User::factory()->create();
        $message = 'Hello World';
        $messageGroup = MessageGroup::factory()->create();
        $messageGroup->users()->attach($user->id);
        $round = Round::factory()->create([
            'host_id' => $user->id,
            'message_group_id' => $messageGroup->id,
        ]);

        $payload = [
            'message' => $message,
        ];

        $response = $this->actingAs($user)->postJson(
            route('api.v1.message.store', [
                'message_group_id' => $round->message_group_id,
            ]), $payload);

        $this->assertDatabaseHas('messages', [
            'user_id' => $user->id,
            'message_group_id' => $round->message_group_id,
            'message' => $message,
        ]);

        $this->assertEquals($payload['message'], $response->json('message'));
        $this->assertEquals($user->id, $response->json('user_id'));
        $this->assertEquals($messageGroup->id, $response->json('message_group_id'));
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

    public function test_returns_403_if_the_user_is_not_active_member_of_message_group(): void
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

    public function test_returns_403_if_the_message_group_is_not_active(): void
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
}
