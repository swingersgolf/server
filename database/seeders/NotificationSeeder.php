<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Enums\NotificationType;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch all user IDs to associate notifications with them
        $userIds = User::pluck('id')->toArray();

        // Generate notifications for each user
        foreach ($userIds as $userId) {
            Notification::factory()->count(5)->create([
                'user_id' => $userId,
                'type' => $this->randomNotificationType(),
                'data' => $this->generateRandomData(),
                'read_at' => $this->randomReadAt(),
            ]);
        }
    }

    /**
     * Generate a random notification type.
     *
     * @return string
     */
    private function randomNotificationType(): string
    {
        return NotificationType::cases()[array_rand(NotificationType::cases())]->value;
    }

    /**
     * Generate random data for the notification.
     *
     * @return array
     */
    private function generateRandomData(): array
    {
        return [
            'message' => fake()->sentence(),  // Use Laravel's Faker for a random message
            'details' => fake()->paragraph(),  // Add additional details if needed
        ];
    }

    /**
     * Generate a random read_at timestamp or null.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    private function randomReadAt(): ?\Illuminate\Support\Carbon
    {
        return rand(0, 1) ? Carbon::now()->subMinutes(rand(0, 10080)) : null; // 10080 minutes = 7 days
    }
}
