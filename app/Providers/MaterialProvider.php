<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\UserService\MaterialServiceInterface;
use App\Services\UserService\MaterialService;
class MaterialProvider extends ServiceProvider
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
            MaterialServiceInterface::class,
            MaterialService::class,

        );
    }
}