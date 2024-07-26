<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // $this->app->singleton(Messaging::class, function ($app) {
        //     $firebase = (new Factory)
        //         ->withServiceAccount(config('firebase.credentials.file'))
        //         ->withProjectId(config('firebase.project_id'))
        //         ->createMessaging();
            
        //     return $firebase;
        // });
    }

    public function boot()
    {
        //
    }
}



















// namespace App\Providers;


// use GuzzleHttp\Client;
// use GuzzleHttp\ClientInterface;



// use Illuminate\Support\ServiceProvider;
// use Illuminate\Support\Facades\Schema;
// class AppServiceProvider extends ServiceProvider
// {
//     /**
//      * Register any application services.
//      */
//     public function register(): void
//     {
//         $this->app->singleton(ClientInterface::class, function ($app) {
//             return new Client();
//         });
//     }

//     /**
//      * Bootstrap any application services.
//      */
//     public function boot(): void
//     {
       
//     }
// }
