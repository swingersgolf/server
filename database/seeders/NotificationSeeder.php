<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;  // Make sure to import this one

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
            // Generate 3 notifications in each of the time ranges
            foreach (['today', '1-6_days', '7-31_days', '31-364_days', '365_plus_days'] as $range) {
                Notification::factory()->count(3)->create([
                    'user_id' => $userId,
                    'created_at' => $this->getRandomDateForRange($range),
                    'data' => $this->generateRandomData(),
                    'read_at' => $this->randomReadAt(),
                ]);
            }
        }
    }

    /**
     * Generate a random date for the specified time range.
     *
     * @param string $range
     * @return \Illuminate\Support\Carbon
     */
    private function getRandomDateForRange(string $range): \Illuminate\Support\Carbon
    {
        switch ($range) {
            case 'today':
                return Carbon::today();
            case '1-6_days':
                return Carbon::now()->subDays(rand(1, 6));
            case '7-31_days':
                return Carbon::now()->subDays(rand(7, 31));
            case '31-364_days':
                return Carbon::now()->subDays(rand(31, 364));
            case '365_plus_days':
                return Carbon::now()->subDays(rand(365, 730)); // 1-2 years ago
            default:
                return Carbon::now();
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
