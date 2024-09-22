<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Course;
use App\Models\Round;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoundControllerTest extends TestCase
{

    public function test_it_returns_all_rounds(): void
    {
        $user = User::factory()->create();
        Round::factory()->count(3)->create();
        $response = $this->actingAs($user)->get(route('api.v1.round.index'))
            ->assertSuccessful();
        $response->assertJsonCount(3);
    }

    public function test_it_returns_a_round_with_attributes(): void
    {
        $user = User::factory()->create();
        $where = Course::factory()->create([
            'name' => 'Some Course Name'
        ]);
        $round = Round::factory()->create([
            'when' => now(),
            'course_id' => $where->id,
        ]);


        $response = $this->actingAs($user)->get(route('api.v1.round.index'))
            ->assertSuccessful();
        $responseData = $response->json()['data'][0];
        $this->assertEquals($round->when, $responseData['when']);

        $courseName = Course::find($round->course_id)->name;
        $this->assertEquals($courseName, $responseData['course']);
    }
}
