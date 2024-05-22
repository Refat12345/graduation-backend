<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\UserService\MedicalAnalysisServiceInterface;
use App\Services\UserService\MedicalAnalysisService;
class MedicalAnalysisProvider extends ServiceProvider
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
            MedicalAnalysisServiceInterface::class,
            MedicalAnalysisService::class,

        );
    }
}
