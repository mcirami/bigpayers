<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/19/2018
 * Time: 4:52 PM
 */

namespace LeadMax\TrackYourStats\Database\Stubs;


class ClickRegister implements Stub
{

    public $affiliateIds;

    public $offerIds;

    public $offerUrl;

    public $report;

    public $registerShuffle;

    const SHUFFLE_ONE_TO_ONE = 0;

    const SHUFFLE_RANDOM = 1;

    public function __construct($offerUrl = "", $affiliateIds = [], $offerIds = [])
    {
        $this->offerUrl = $offerUrl;

        $this->affiliateIds = $affiliateIds;

        $this->offerIds = $offerIds;
    }

    public function setRegistrationShuffle($registerShuffle)
    {
        $this->registerShuffle = $registerShuffle;
    }


    private function registerClicks()
    {
        foreach ($this->affiliateIds as $userId) {
            foreach ($this->offerIds as $offerId) {
                for ($i = 0; $i < 20; $i++) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $this->offerUrl."?repid={$userId}&offerid={$offerId}&sub1=&price=.5");

                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_USERAGENT, "THICCBOI");

                    $output = curl_exec($ch);

                    $this->report[] = $output;

                    curl_close($ch);
                }
            }
        }
    }


    public function run()
    {
        $this->registerClicks();
    }


    public function generateReport()
    {
        return $this->report;
    }

    public function getReportTitle()
    {
        return "Click Registration Stub";
    }
}