<?php

namespace App\Providers;

use App\Services\SMS\ShortMessageServiceInterface;
use App\Services\SMS\Text69;
use Illuminate\Support\ServiceProvider;

class SMSApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ShortMessageServiceInterface::class, function($app){
            return new Text69();
        });
    }
}
