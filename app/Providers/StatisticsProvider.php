<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\UserService\StatisticsServiceInterface ;
use App\Services\UserService\StatisticsService ;
class StatisticsProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
     
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(
            StatisticsServiceInterface::class,
            StatisticsService::class,

        );
    }
}

