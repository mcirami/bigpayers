<?php

namespace App\Support\Tracking\Events\Listeners;


use App\Support\ClickIdCodec as UID;
use App\Support\NativeRequest;
use App\Support\Tracking\Events\DeductionRegistrationEvent;

class DeductionListener extends Listener
{

    public $GETRequirements = ["clickid", "function"];

    public function dispatch()
    {
        $clickId = UID::decode(NativeRequest::query('clickid'));
        $register = new DeductionRegistrationEvent($clickId);

        return $register->fire();
    }


    public function shouldBeDispatched()
    {
        if ($this->checkGETRequirements()) {
            if (NativeRequest::query('function') == DeductionRegistrationEvent::getEventString()) {
                return true;
            }
        }

        return false;
    }

}
