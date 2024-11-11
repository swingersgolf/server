<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

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
                'data' => $this->generateRandomData(),
                'read_at' => $this->randomReadAt(),
            ]);
        }
    }

    /**
     * Generate random data for the notification.
     *
     * @return array
     */
    private function generateRandomData(): array
    {
        return [
            'to' => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'data' => (object) [
                'type' => fake()->randomElement(['round_accepted', 'round_rejected', 'round_requested']),
                'route' => '(round)/details',
                'params' => (object) [
                    'roundId' => fake()->numberBetween(1, 10),
                ],
            ],
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
