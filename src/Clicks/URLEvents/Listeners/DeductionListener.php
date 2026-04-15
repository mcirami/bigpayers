<?php

namespace LeadMax\TrackYourStats\Clicks\URLEvents\Listeners;


use LeadMax\TrackYourStats\Clicks\UID;
use LeadMax\TrackYourStats\Clicks\URLEvents\DeductionRegistrationEvent;

class DeductionListener extends Listener
{

    public $GETRequirements = ["clickid", "function"];

    public function dispatch()
    {
        $clickId = UID::decode($_GET["clickid"]);
        $register = new DeductionRegistrationEvent($clickId);

        return $register->fire();
    }


    public function shouldBeDispatched()
    {
        if ($this->checkGETRequirements()) {
            if ($_GET["function"] == DeductionRegistrationEvent::getEventString()) {
                return true;
            }
        }

        return false;
    }

}