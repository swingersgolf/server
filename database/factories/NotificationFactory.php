<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a notification at a random time within the specified ranges
        $createdAt = $this->generateRandomDate();

        return [
            'user_id' => User::factory(),
            'data' => $this->generateRandomData(),
            'read_at' => $this->randomReadAt(),
            'created_at' => $createdAt, // Set the created_at to the randomly generated date
            'updated_at' => $createdAt, // Ensure updated_at matches created_at
        ];
    }

    /**
     * Generate a random timestamp for one of the time ranges.
     *
     * @return \Illuminate\Support\Carbon
     */
    private function generateRandomDate(): Carbon
    {
        // Define the time ranges for notifications
        $range = $this->faker->randomElement([
            'today',
            '1-6_days',
            '7-31_days',
            '31-364_days',
            '365_plus_days'
        ]);

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
