<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\UserService\AppointmentServiceInterface;
use App\Services\UserService\AppointmentService;
class AppointmentProvider extends ServiceProvider
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
            AppointmentServiceInterface::class,
            AppointmentService::class,

        );
    }
}