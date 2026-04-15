<?php

namespace App\Providers;

use App\Services\DBWhiteLabelService;
use Illuminate\Support\ServiceProvider;

class DBWhiteLabelProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ( ! app()->runningInConsole()) {
            $dbWhiteLabel = new DBWhiteLabelService(request()->getHttpHost());
            $dbWhiteLabel->findCompanySubDomain();
            $dbWhiteLabel->changeDatabaseHostWithSubDomain();



        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
