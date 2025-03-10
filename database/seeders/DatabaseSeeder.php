<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'expo_push_token' => config('expo.push_token'),
        ]);

        $this->call([
            PreferenceSeeder::class,
            UserSeeder::class,
            CourseSeeder::class,
            RoundSeeder::class,
            NotificationSeeder::class,
            PreferenceUserSeeder::class,
        ]);
    }
}
