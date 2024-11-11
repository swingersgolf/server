<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index()
    {
        $user = User::factory()->create();

        $notifications = Notification::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('api.v1.notification.index'))
            ->assertOk();

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_user()
    {
        $user = User::factory()->create();
        $notifications = Notification::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('api.v1.notification.user'))
            ->assertOk();

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonFragment(['user_id' => $user->id]);
    }

    public function test_read()
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create();

        $response = $this->actingAs($user)->patch(route('api.v1.notification.read', $notification->id))
            ->assertOk();

        $response->assertStatus(200);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);

        $response->assertJson(['message' => 'Notification marked as read.']);
    }

    public function test_unread()
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create();
        $notification->read_at = now();
        $notification->save();

        $response = $this->actingAs($user)->patch(route('api.v1.notification.unread', $notification->id))
            ->assertOk();

        $response->assertStatus(200);

        $notification->refresh();
        $this->assertNull($notification->read_at);

        $response->assertJson(['message' => 'Notification marked as unread.']);
    }

    public function test_destroy()
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create();

        $response = $this->actingAs($user)->delete(route('api.v1.notification.delete', $notification->id))
            ->assertOk();

        $response->assertStatus(200);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);

        $response->assertJson(['message' => 'Notification deleted.']);
    }
}
