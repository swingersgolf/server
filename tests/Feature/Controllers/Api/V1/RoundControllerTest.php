<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Attribute;
use App\Models\Course;
use App\Models\Round;
use App\Models\User;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoundControllerTest extends TestCase
{
    public function test_index_returns_all_rounds(): void
    {
        $user = User::factory()->create();
        $rounds = Round::factory()->count(3)->create();
        $response = $this->actingAs($user)->get(route('api.v1.round.index'))
            ->assertSuccessful();
        $this->assertEquals($rounds->count(), count($response->json()['data']));
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

    #[DataProvider('whenScenarios')]
    public function test_index_returns_rounds_filtered_by_dates($start, $end, $count): void
    {
        Carbon::setTestNow(now());
        $user = User::factory()->create();
        Round::factory()->create([
            'when' => now()->addDays(2)->format('Y-m-d H:i'),
        ]);
        Round::factory()->create([
            'when' => now()->addDays(4)->format('Y-m-d H:i'),
        ]);
        $response = $this->actingAs($user)->getJson(
            route('api.v1.round.index', [
                'start' => now()->addDays($start)->format('Y-m-d H:i'),
                'end' => now()->addDays($end)->format('Y-m-d H:i'),
            ]))
            ->assertSuccessful();
        $this->assertEquals($count, count($response->json()['data']));
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

    public static function whenScenarios(): array
    {
        return [
            'all rounds' => [
                'start' => 1,
                'end' => 5,
                'count' => 2,
            ],
            'second round only' => [
                'start' => 3,
                'end' => 5,
                'count' => 1,
            ],
            'first round only' => [
                'start' => 0,
                'end' => 3,
                'count' => 1,
            ],
            'out of range' => [
                'start' => 6,
                'end' => 9,
                'count' => 0,
            ]
        ];
    }
}
