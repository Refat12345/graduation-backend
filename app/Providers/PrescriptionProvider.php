<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\UserService\PrescriptionServiceInterface;
use App\Services\UserService\PrescriptionService;
class PrescriptionProvider extends ServiceProvider
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
            PrescriptionServiceInterface::class,
            PrescriptionService::class,

        );
    }
}
