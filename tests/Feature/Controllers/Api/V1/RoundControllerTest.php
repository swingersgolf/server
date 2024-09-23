<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Attribute;
use App\Models\Course;
use App\Models\Round;
use App\Models\User;
use Tests\TestCase;

class RoundControllerTest extends TestCase
{
    public function test_index_returns_all_rounds(): void
    {
        $user = User::factory()->create();
        $rounds = Round::factory()->count(3)->create();
        $response = $this->actingAs($user)->get(route('api.v1.round.index'))
            ->assertSuccessful();
        $this->assertEquals($rounds->count(),count($response->json()['data']));
    }

    public function test_index_returns_rounds_with_attributes(): void
    {
        $users = User::factory()->count(3)->create();
        $where = Course::factory()->create([
            'name' => 'Some Course Name',
        ]);
        $attributes = Attribute::factory()->count(3)->create();

        $round = Round::factory()->create([
            'when' => now(),
            'course_id' => $where->id,
        ]);

        $round->attributes()->attach($attributes);
        $round->users()->attach($users);

        $response = $this->actingAs($users[0])->get(route('api.v1.round.index'))
            ->assertSuccessful();
        $responseData = $response->json()['data'][0];

        $this->assertEquals($round->when, $responseData['when']);

        $courseName = Course::find($round->course_id)->name;
        $this->assertEquals($courseName, $responseData['course']);

        $this->assertCount($attributes->count(), $responseData['attributes']);
        $attributes->map(function ($attribute) use ($responseData) {
            return in_array($attribute->name, $responseData['attributes']) &&
                in_array($attribute->id, $responseData['attributes']);
        });
        $this->assertCount($attributes->count(), $responseData['attributes']);

        $this->assertCount($users->count(), $responseData['users']);
    }
    public function test_show_returns_round(): void
    {
        $users = User::factory()->count(3)->create();
        $where = Course::factory()->create([
            'name' => 'Some Course Name',
        ]);
        $attributes = Attribute::factory()->count(3)->create();

        $round = Round::factory()->create([
            'when' => now(),
            'course_id' => $where->id,
        ]);

        $round->attributes()->attach($attributes);
        $round->users()->attach($users);

        $response = $this->actingAs($users[0])->get(route('api.v1.round.show', $round->id))
            ->assertSuccessful();
        $responseData = $response->json()['data'];

        $this->assertEquals($round->when, $responseData['when']);

        $courseName = Course::find($round->course_id)->name;
        $this->assertEquals($courseName, $responseData['course']);

        $this->assertCount($attributes->count(), $responseData['attributes']);
        $attributes->map(function ($attribute) use ($responseData) {
            return in_array($attribute->name, $responseData['attributes']) &&
                in_array($attribute->id, $responseData['attributes']);
        });
        $this->assertCount($attributes->count(), $responseData['attributes']);

        $this->assertCount($users->count(), $responseData['users']);
    }
}
