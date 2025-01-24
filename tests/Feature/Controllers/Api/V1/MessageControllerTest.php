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
    public function test_it_can_post_a_message_and_returns_a_message(): void
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
}
