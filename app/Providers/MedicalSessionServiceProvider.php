<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\UserService\MedicalSessionServiceInterface;
use App\Services\UserService\MedicalSessionService;
class MedicalSessionServiceProvider extends ServiceProvider
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
            MedicalSessionServiceInterface::class,
            MedicalSessionService::class,


        );
    }
}
