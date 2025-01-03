<?php

namespace App\Providers;

use App\Services\AwsProfilePhotoService;
use App\Services\ProfilePhotoServiceInterface;
use Illuminate\Support\ServiceProvider;

class ProfilePhotoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ProfilePhotoServiceInterface::class, AwsProfilePhotoService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
