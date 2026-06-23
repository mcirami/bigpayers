<?php

namespace App\Providers;

use App\Services\CompanyDatabaseConnectionManager;
use App\Services\DBWhiteLabelService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class DBWhiteLabelProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        if ( ! app()->runningInConsole()) {
            $dbWhiteLabel = new DBWhiteLabelService($request->getHttpHost(), app(CompanyDatabaseConnectionManager::class));
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
