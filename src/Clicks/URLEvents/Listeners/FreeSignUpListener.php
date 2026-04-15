<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/21/2018
 * Time: 3:04 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents\Listeners;


use LeadMax\TrackYourStats\Clicks\UID;
use LeadMax\TrackYourStats\Clicks\URLEvents\FreeSignUpRegistrationEvent;

class FreeSignUpListener extends Listener
{

    public $GETRequirements = ["clickid", "function"];

    public function dispatch()
    {
        $clickId = UID::decode($_GET["clickid"]);
        $register = new FreeSignUpRegistrationEvent($clickId);

        return $register->fire();
    }

    public function shouldBeDispatched()
    {
        if ($this->checkGETRequirements()) {
            if ($_GET["function"] == FreeSignUpRegistrationEvent::getEventString()) {
                return true;
            }
        }

        return false;
    }

}