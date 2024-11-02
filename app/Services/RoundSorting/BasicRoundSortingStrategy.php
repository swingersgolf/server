<?php

namespace App\Services\RoundSorting;

class BasicRoundSortingStrategy implements RoundSortingStrategyInterface
{
    public function sort($rounds)
    {
        return $rounds->sortBy('created_at')->values();
    }
}
