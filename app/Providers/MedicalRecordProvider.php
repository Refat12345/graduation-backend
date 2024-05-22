<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\UserService\MedicalRecordServiceInterface;
use App\Services\UserService\MedicalRecordService;
class MedicalRecordProvider extends ServiceProvider
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
            MedicalRecordServiceInterface::class,
            MedicalRecordService::class,

        );
    }
}