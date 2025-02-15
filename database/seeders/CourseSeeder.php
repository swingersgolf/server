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
            // Canada Golf Courses
            ['course_name' => 'Abitibi Golf Club', 'city_name' => 'Abitibi', 'latitude' => '48.76344156470971', 'longitude' => '-80.67824213076054'],
            ['course_name' => 'Bay of Quinte Country Club', 'city_name' => 'Belleville', 'latitude' => '44.13651117041213', 'longitude' => '-77.44096934640667'],
            ['course_name' => 'Dragon Hills Golf Course & Driving Range', 'city_name' => 'Dragon Hills', 'latitude' => '48.50079841545775', 'longitude' => '-89.2459058749579'],
            ['course_name' => 'Lionhead Golf Club', 'city_name' => 'Brampton', 'latitude' => '43.64192242495643', 'longitude' => '-79.78788656662748'],
            ['course_name' => 'Tyandaga Golf Course', 'city_name' => 'Burlington', 'latitude' => '43.349776318986535', 'longitude' => '-79.84646998878374'],
            ['course_name' => 'Pembroke Golf Club', 'city_name' => 'Pembroke', 'latitude' => '45.849938720994075', 'longitude' => '-77.17102608862577'],  
            // OSU Golf Courses
            ['course_name' => 'The Ohio State University Golf Club Gray Course', 'city_name' => 'Columbus', 'latitude' => '40.0323360541304', 'longitude' => '-83.05326786330427'],
            ['course_name' => 'The Ohio State University Golf Club Scarlet Course', 'city_name' => 'Columbus', 'latitude' => '40.0323360541304', 'longitude' => '-83.05326786330427'],
            ['course_name' => 'The Ohio State University PGM Simulator', 'city_name' => 'Columbus', 'latitude' => '40.002727902045585', 'longitude' => '-83.02814496106552'],
        ];

        // Loop through the courses array and create each course in the database
        foreach ($courses as $course) {
            Course::factory()->create([
                'course_name' => $course['course_name'],
                'city_name' => $course['city_name'],
                'latitude' => $course['latitude'],
                'longitude' => $course['longitude'],
            ]);
        }
    }
}
