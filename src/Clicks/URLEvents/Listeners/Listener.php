<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/21/2018
 * Time: 2:19 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents\Listeners;

use LeadMax\TrackYourStats\Clicks\TrackingParameters;

abstract class Listener
{
    protected $GETRequirements = [];

    abstract function shouldBeDispatched();


    abstract function dispatch();


    protected function checkGETRequirements()
    {
        $params = TrackingParameters::normalize($_GET);

        foreach ($this->GETRequirements as $var) {
            if (!TrackingParameters::has($params, $var) || TrackingParameters::get($params, $var) === '') {
                return false;
            }
        }

        return true;
    }


}
