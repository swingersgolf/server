<?php

namespace App\Providers;

use App\Services\RoundSorting\SortByCreateStrategy;
use App\Services\RoundSorting\RoundSortingStrategyInterface;
use Illuminate\Support\ServiceProvider;

class RoundSortingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(RoundSortingStrategyInterface::class, SortByCreateStrategy::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
