<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Enums\NotificationType;

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
        return [
            'type' => $this->faker->randomElement(NotificationType::cases())->value,
            'user_id' => User::factory(),
            'data' => $this->faker->sentence(),
            'read_at' => $this->faker->optional()->dateTime(),
        ];
    }    
}
