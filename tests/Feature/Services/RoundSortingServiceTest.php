<?php

namespace Tests\Feature\Services;

use App\Models\Preference;
use App\Models\Round;
use App\Models\User;
use App\Services\RoundSorting\RoundSortingService;
use App\Services\RoundSorting\SortByCreateStrategy;
use App\Services\RoundSorting\SortByPreferencesStrategy;
use Tests\TestCase;

class RoundSortingServiceTest extends TestCase
{
    public function test_it_sorts_rounds_by_create_dated(): void
    {
        $sortingStrategy = new SortByCreateStrategy;
        $roundSortingService = new RoundSortingService($sortingStrategy);

        $round1 = Round::factory()->create([
            'created_at' => now()->subDays(5),
        ]);
        $round2 = Round::factory()->create([
            'created_at' => now()->addDays(5),
        ]);
        $round3 = Round::factory()->create([
            'created_at' => now(),
        ]);

        $rounds = collect([$round1, $round2, $round3]);

        $sortedRounds = $roundSortingService->sortRounds($rounds);

        $this->assertCount(3, $sortedRounds);

        $this->assertEquals($sortedRounds[0]->id, $round1->id);
        $this->assertEquals($sortedRounds[1]->id, $round3->id);
        $this->assertEquals($sortedRounds[2]->id, $round2->id);
    }

    public function test_it_sorts_rounds_by_preference_match(): void
    {
        $sortingStrategy = new SortByPreferencesStrategy;
        $roundSortingService = new RoundSortingService($sortingStrategy);

        $drinking = Preference::factory()->create([
            'name' => 'drinking',
        ]);
        $dancing = Preference::factory()->create([
            'name' => 'dancing',
        ]);

        $round1 = Round::factory()->create([]);
        $round1->preferences()->attach($drinking, ['status' => Preference::STATUS_INDIFFERENT]);
        $round1->preferences()->attach($dancing, ['status' => Preference::STATUS_INDIFFERENT]);

        $round2 = Round::factory()->create([]);
        $round2->preferences()->attach($drinking, ['status' => Preference::STATUS_PREFERRED]);
        $round2->preferences()->attach($dancing, ['status' => Preference::STATUS_DISLIKED]);

        $round3 = Round::factory()->create([]);
        $round3->preferences()->attach($drinking, ['status' => Preference::STATUS_PREFERRED]);
        $round3->preferences()->attach($dancing, ['status' => Preference::STATUS_INDIFFERENT]);

        $rounds = collect([$round1, $round2, $round3]);

        $user = User::factory()->create();
        $user->preferences()->attach($drinking, ['status' => Preference::STATUS_PREFERRED]);
        $user->preferences()->attach($dancing, ['status' => Preference::STATUS_DISLIKED]);
        $this->actingAs($user);

        $sortedRounds = $roundSortingService->sortRounds($rounds);

        $this->assertEquals($sortedRounds[0]->id, $round2->id);
        $this->assertEquals($sortedRounds[1]->id, $round3->id);
        $this->assertEquals($sortedRounds[2]->id, $round1->id);
    }
}
