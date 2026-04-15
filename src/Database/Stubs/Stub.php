<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/19/2018
 * Time: 4:41 PM
 */

namespace LeadMax\TrackYourStats\Database\Stubs;

interface Stub
{

    public function run();

    public function generateReport();

    public function getReportTitle();

}