<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/21/2018
 * Time: 2:24 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents\Listeners;

use App\Support\TrackingParameters;
use App\Support\LegacyClickRegistrationEvent as ClickRegistrationEvent;
use App\Support\NativeRequest;

class ClickListener extends Listener
{

    public $GETRequirements = ["repid", "offerid", "function"];


    public function dispatch()
    {
        $params = TrackingParameters::normalize(NativeRequest::queryAll());

        $ip = NativeRequest::clientIp();

        $register = new ClickRegistrationEvent(
            TrackingParameters::get($params, "repid"),
            TrackingParameters::get($params, "offerid"),
            $params,
            $ip
        );

        return $register->fire();
    }


    public function shouldBeDispatched()
    {
        $params = TrackingParameters::normalize(NativeRequest::queryAll());
        if ($this->checkGETRequirements()) {
            if (($params["function"] ?? null) == ClickRegistrationEvent::getEventString()) {
                return true;
            }
        }

        return false;
    }

}
