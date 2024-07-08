<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\MedicalAnalysisProvider::class,
    App\Providers\MedicalSessionServiceProvider::class,
    App\Providers\ServiceServiceProvider::class,
    App\Providers\MedicalRecordProvider::class,
    App\Providers\PrescriptionProvider::class,
    App\Providers\MaterialProvider::class,
    App\Providers\StatisticsProvider::class,
    App\Providers\AppointmentProvider::class,
 //   App\Providers\RequestsServiceProvider::class,
    App\Providers\RequestsServiceProvider::class,
    Kreait\Laravel\Firebase\ServiceProvider::class,
];
