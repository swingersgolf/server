<?php

namespace App\Services\RoundSorting;

class SortByCreateStrategy implements RoundSortingStrategyInterface
{
    public function sort($rounds)
    {
        return $rounds->sortBy('created_at')->values();
    }
}
