<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Enums\NotificationType;
use Faker\Factory as Faker;

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
                'data' => $this->generateRandomData($userId),
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
     * @param int $userId
     * @return array
     */
    private function generateRandomData(string $userId): array
    {
        $faker = Faker::create();

        return [
            'to' => User::find($userId)->expo_push_token ?? 'ExponentPushToken[XXXXXXXXXXXXXXXXXXXXXXXX]', // Use an existing user's Expo push token or a default value
            'title' => $faker->sentence(rand(3, 6)), // A random title with 3 to 6 words
            'body' => $faker->paragraph(rand(1, 3)), // A random body with 1 to 3 sentences
            'data' => [], // Additional random data can be added here if needed
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
