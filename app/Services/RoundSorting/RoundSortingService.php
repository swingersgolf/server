<?php

namespace App\Services\RoundSorting;

class RoundSortingService
{
    protected RoundSortingStrategyInterface $strategy;

    public function __construct(RoundSortingStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function sortRounds($rounds)
    {
        return $this->strategy->sort($rounds);
    }
}
