<?php

namespace Database\Factories;

use App\Models\Preference;
use App\Models\User;
use App\Models\PreferenceUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class PreferenceUserFactory extends Factory
{
    protected $model = PreferenceUser::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),  // Generate a user using the User factory
            'preference_id' => Preference::factory(),  // Generate a preference using the Preference factory
            'status' => $this->faker->randomElement(['indifferent', 'disliked', 'preferred']),
        ];
    }
}
