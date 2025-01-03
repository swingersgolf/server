<?php

namespace App\Providers;

use App\Services\ProfilePhotoService;
use App\Services\ProfilePhotoServiceInterface;
use Illuminate\Support\ServiceProvider;

class ProfilePhotoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ProfilePhotoServiceInterface::class, ProfilePhotoService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
