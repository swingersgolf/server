<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = collect([
            'Abitibi Golf Club',
            'Bay of Quinte Country Club',
            'Dragon Hills Golf Course & Driving Range',
            'Lionhead Golf Club',
            'Tyandaga Golf Course',
            'Pembroke Golf Club'
        ]);
        $courses->each(function($course) {
            Course::factory()->create([
                'name' => $course,
            ]);
        });
    }
}
