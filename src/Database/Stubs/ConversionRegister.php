<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/19/2018
 * Time: 4:44 PM
 */

namespace LeadMax\TrackYourStats\Database\Stubs;


use LeadMax\TrackYourStats\Clicks\UID;

class ConversionRegister implements Stub
{

    public $postBackUrl;

    public $clickIds;

    public $report = [];


    public function __construct($clickids = [])
    {
        $this->clickIds = $clickids;
    }


    public function run()
    {
        $this->encodeClickIds();
        $this->registerClicks();
    }


    public function generateReport()
    {
        return $this->report;
    }

    public function getReportTitle()
    {
        return "Conversion Registration Stub";
    }

    public function encodeClickIds()
    {
        foreach ($this->clickIds as &$clickId) {
            $clickId = UID::encode($clickId);
        }
    }

    public function registerClicks()
    {
        foreach ($this->clickIds as $clickId) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->postBackUrl.$clickId);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $output = curl_exec($ch);

            $this->report[$clickId] = $output;

            curl_close($ch);
        }
    }


    public function addClicksIds(array $clickIds)
    {
        $this->clickIds = array_merge($this->clickIds, $clickIds);
    }

}