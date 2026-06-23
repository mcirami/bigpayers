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

class DBWhiteLabelService
{
    public $subDomain;

    public $url;

    private $connections;

    public function __construct($url, ?CompanyDatabaseConnectionManager $connections = null)
    {
        $this->url = $url;
        $this->connections = $connections ?: app(CompanyDatabaseConnectionManager::class);
    }

    public function changeDatabaseHostWithSubDomain()
    {
        $this->connections->configureNamed('mysql', $this->subDomain);
    }


    public static function getSubDomain(string $host)
    {
        $host = static::normalizeHost($host);
        $sub = explode(".", $host);

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

        $this->subDomain = self::getSubDomain($url);


        // checks if its on live test server (test.trackyourstats.com)
        // this is required because 'test' database name was taken.
        $this->checkAndSetIfStagingServer();


    }


    public function checkAndSetIfLoginPageOrLanderPage($url)
    {
        $hosts = $this->candidateHosts($url);
        $company = Company::whereIn('login_url', $hosts)->orWhereIn('landing_page', $hosts)->first();

        if (is_null($company) == false) {
            $this->subDomain = $company->subDomain;

            return true;
        }

        return false;
    }

    public function checkAndSetIfOfferUrl($url)
    {
        $offerUrl = OfferURL::whereIn('url', $this->candidateHosts($url))->first();

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
        if ($this->subDomain == 'test') {
            $this->subDomain = 'debug';
        }

    }

    private static function normalizeHost(string $host): string
    {
        $normalized = strtolower(trim($host));
        $normalized = preg_replace('/:\d+$/', '', $normalized);
        $normalized = rtrim($normalized, '.');

        if (str_starts_with($normalized, 'www.')) {
            return substr($normalized, 4);
        }

        return $normalized;
    }

    private function candidateHosts(string $host): array
    {
        $normalized = static::normalizeHost($host);
        $hosts = [$normalized];

        if (!str_starts_with(strtolower(trim($host)), 'www.')) {
            $hosts[] = 'www.' . $normalized;
        }

        return array_values(array_unique($hosts));
    }


}
