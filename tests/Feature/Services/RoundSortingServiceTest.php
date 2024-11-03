<?php

namespace Tests\Feature\Services;

use App\Models\Round;
use App\Services\RoundSorting\SortByCreateStrategy;
use App\Services\RoundSorting\RoundSortingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoundSortingServiceTest extends TestCase
{
    public function test_it_sorts_rounds(): void
    {
        $sortingStrategy = new SortByCreateStrategy();
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
}
