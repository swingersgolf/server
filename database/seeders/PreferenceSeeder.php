<?php

namespace Database\Seeders;

use App\Models\Preference;
use Illuminate\Database\Seeder;

class PreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $preferences = collect(['Drinking', 'Walking', 'Betting', 'Music']);
        $preferences->each(function ($preference) {
            Preference::factory()->create([
                'name' => $preference,
            ]);
        });
    }
}
