<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = collect(['Drinking', 'Walking', 'Betting', 'Music']);
        $attributes->each(function($attribute) {
            Attribute::factory()->create([
                'name' => $attribute,
            ]);
        });
    }
}
