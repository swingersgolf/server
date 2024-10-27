<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define an associative array with course names and their corresponding city names
        $courses = [
            ['course_name' => 'Abitibi Golf Club', 'city_name' => 'Abitibi'],
            ['course_name' => 'Bay of Quinte Country Club', 'city_name' => 'Belleville'],
            ['course_name' => 'Dragon Hills Golf Course & Driving Range', 'city_name' => 'Dragon Hills'],
            ['course_name' => 'Lionhead Golf Club', 'city_name' => 'Brampton'],
            ['course_name' => 'Tyandaga Golf Course', 'city_name' => 'Burlington'],
            ['course_name' => 'Pembroke Golf Club', 'city_name' => 'Pembroke'],
        ];

        // Loop through the courses array and create each course in the database
        foreach ($courses as $course) {
            Course::factory()->create([
                'course_name' => $course['course_name'],
                'city_name' => $course['city_name'],
            ]);
        }
    }
}
