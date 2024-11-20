<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index()
    {
        // Create a user to act as the authenticated user
        $user = User::factory()->create();

        // Create 3 courses
        $courses = Course::factory()->count(3)->create();

        // Make a GET request to the courses index route
        $response = $this->actingAs($user)->get(route('api.v1.course.index'))
            ->assertOk();

        // Assert that the response status is OK (200)
        $response->assertStatus(200);

        // Assert that the response contains 3 courses in the 'data' field
        $response->assertJsonCount(3, 'data');
    }
}
