<?php

namespace App\Support\Tracking\Events\Listeners;

use App\Support\ClickIdCodec as UID;
use App\Support\NativeRequest;
use App\Support\Tracking\Events\ConversionRegistrationEvent;

class ConversionListener extends Listener
{

    public $GETRequirements = ["clickid"];


    public function dispatch()
    {
        $customPayout = NativeRequest::query('price', false);
        $clickId      = UID::decode(NativeRequest::query('clickid'));
        $register     = new ConversionRegistrationEvent($clickId, $customPayout);

        return $register->fire();
    }

    public function shouldBeDispatched()
    {
        if ($this->checkGETRequirements()) {
            if (NativeRequest::query('function') === null || NativeRequest::query('function') == "") {
                return true;
            }
        }

        return false;
    }

}
