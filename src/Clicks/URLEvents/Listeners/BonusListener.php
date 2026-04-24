<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/23/2018
 * Time: 11:37 AM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents\Listeners;


use LeadMax\TrackYourStats\Clicks\TrackingParameters;
use LeadMax\TrackYourStats\Clicks\URLEvents\BonusRegistrationEvent;

class BonusListener extends Listener
{

    public $GETRequirements = ['repid', 'function', 'bonusid'];

    public function dispatch()
    {
        $params = TrackingParameters::normalize($_GET);
        $register = new BonusRegistrationEvent($params["bonusid"], TrackingParameters::get($params, "repid"));

        return $register->fire();
    }

    public function shouldBeDispatched()
    {
        $params = TrackingParameters::normalize($_GET);
        if ($this->checkGETRequirements()) {
            if (($params["function"] ?? null) == BonusRegistrationEvent::getEventString()) {
                return true;
            }
        }


        return false;
    }

}
