<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\Round;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    public function test_it_posts_message_and_returns_message(): void
    {
        $user = User::factory()->create();
        $message = 'Hello World';
        $messageGroup = MessageGroup::factory()->create();
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
        $round = Round::factory()->create([
            'host_id' => $user->id,
            'message_group_id' => $messageGroup->id,
        ]);

        $payload = [
            'message' => $message,
        ];

        $response = $this->actingAs($user)->postJson(
            route('api.v1.message.store', [
                'message_group_id' => 55,
            ]), $payload)->assertStatus(422);

    }

    public function test_the_user_must_belong_to_the_message_group_user_list(): void
    {
        // TODO: start here and fix this test trying to use messagegrouppolicy
        $user = User::factory()->create();
        $message = 'Hello World';
        $messageGroup = MessageGroup::factory()->create([
            'id' => 5,
        ]);
        $round = Round::factory()->create([
            'host_id' => $user->id,
            'message_group_id' => $messageGroup->id,
        ]);

        $payload = [
            'message' => $message,
        ];

        $response = $this->actingAs($user)->postJson(
            route('api.v1.message.store', [
                'message_group_id' => 5,
            ]), $payload)->assertStatus(422);

    }


}
