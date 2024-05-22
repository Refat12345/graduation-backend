<?php

declare(strict_types=1);

namespace App\Providers;   

use App\Contracts\Services\UserService\UserServiceInterface;
use App\Services\UserService\UserService;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(
            UserServiceInterface::class,
            UserService::class,


        );

    }
}