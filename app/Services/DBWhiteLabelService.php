<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 4/18/2018
 * Time: 2:49 PM
 */

namespace App\Services;

use App\Company;
use App\OfferURL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DBWhiteLabelService
{
    public $subDomain;

    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function changeDatabaseHostWithSubDomain()
    {
        Config::set('database.connections.mysql.database', $this->subDomain);


        //If you want to use query builder without having to specify the connection
//		Config::set('database.default', 'mysql');
//		DB::reconnect('mysql');
    }


    public static function getSubDomain()
    {
        $sub = explode(".", request()->getHttpHost());

        return $sub[0];
    }


    public function findCompanySubDomain()
    {
        $url = $this->url;


        if ($this->checkAndSetIfOfferUrl($url)) {
            return;
        }

        if ($this->checkAndSetIfLoginPageOrLanderPage($url)) {
            return;
        }


        // if it was none of those, default that its a company install e.g. xyz.trackyourstats.com

        $this->subDomain = self::getSubDomain();


        // checks if its on live test server (test.trackyourstats.com)
        // this is required because 'test' database name was taken.
        $this->checkAndSetIfStagingServer();


    }


    public function checkAndSetIfLoginPageOrLanderPage($url)
    {
        $company = Company::where('login_url', $url)->orWhere('landing_page', $url)->first();

        if (is_null($company) == false) {
            $this->subDomain = $company->subDomain;

            return true;
        }

        return false;
    }

    public function checkAndSetIfOfferUrl($url)
    {
        $offerUrl = OfferURL::where('url', $url)->first();

        if (is_null($offerUrl) == false) {
            $company = Company::where('id', $offerUrl->company_id)->first();

            $this->subDomain = $company->subDomain;

            return true;
        }

        return false;
    }

    public function isOfferUrl($url)
    {
        $offerUrl = OfferURL::all()->where('url', '=', $url);

        return $offerUrl->isNotEmpty();
    }

    private function checkAndSetIfStagingServer()
    {
        if (self::getSubDomain() == 'test') {
            $this->subDomain = 'debug';
        }

    }


}