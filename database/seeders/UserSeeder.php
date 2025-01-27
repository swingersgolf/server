<?php

namespace Database\Seeders;

use App\Models\Preference;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test Sender',
            'email' => 'sender@example.com',
            'password' => Hash::make('password'),
            'expo_push_token' => config('expo.push_token'),
        ]);

        User::factory()->create([
            'name' => 'Test Listener',
            'email' => 'listener@example.com',
            'password' => Hash::make('password'),
            'expo_push_token' => config('expo.push_token'),
        ]);

        User::factory()->count(8)->create([
            'password' => Hash::make('password'),
        ]);
    }
}
