<?php

namespace App\Providers;

use App\Services\AwsProfilePhotoService;
use App\Services\MockProfilePhotoService;
use App\Services\ProfilePhotoServiceInterface;
use Illuminate\Support\ServiceProvider;

class ProfilePhotoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ProfilePhotoServiceInterface::class, function() {
            if (app()->environment('testing')) {
                return new MockProfilePhotoService();
            }
            return new AwsProfilePhotoService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
