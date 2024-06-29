<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\UserService\RequestsServiceInterface;
use App\Services\UserService\RequestsService;

class RequestsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            RequestsServiceInterface::class,
            RequestsService::class
        );
    }

    public function boot()
    {
        // Boot services if needed
    }
}

