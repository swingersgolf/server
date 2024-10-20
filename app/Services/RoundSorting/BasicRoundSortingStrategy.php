<?php

namespace App\Services\RoundSorting;

class BasicRoundSortingStrategy implements RoundSortingStrategyInterface
{
    public function sort($rounds)
    {
        $user = auth()->user();
        $userPreferences = $user->preferences;

//        return $rounds->sortBy(function ($round) use ($userPreferences) {
//           // sort logic here
//        });
        return $rounds;
    }
}
