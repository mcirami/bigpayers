<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/23/2018
 * Time: 11:37 AM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents\Listeners;


use LeadMax\TrackYourStats\Clicks\URLEvents\BonusRegistrationEvent;

class BonusListener extends Listener
{

    public $GETRequirements = ['repid', 'function', 'bonusid'];

    public function dispatch()
    {
        $register = new BonusRegistrationEvent($_GET["bonusid"], $_GET["repid"]);

        return $register->fire();
    }

    public function shouldBeDispatched()
    {
        if ($this->checkGETRequirements()) {
            if ($_GET["function"] == BonusRegistrationEvent::getEventString()) {
                return true;
            }
        }


        return false;
    }

}