<?php

namespace App\Providers;

use App\Services\RoundSorting\RoundSortingStrategyInterface;
use App\Services\RoundSorting\SortByPreferencesStrategy;
use Illuminate\Support\ServiceProvider;

class RoundSortingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(RoundSortingStrategyInterface::class, SortByPreferencesStrategy::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
