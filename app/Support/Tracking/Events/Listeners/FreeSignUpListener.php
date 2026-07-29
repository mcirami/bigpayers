<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/21/2018
 * Time: 3:04 PM
 */

namespace App\Support\Tracking\Events\Listeners;


use App\Support\ClickIdCodec as UID;
use App\Support\NativeRequest;
use App\Support\Tracking\Events\FreeSignUpRegistrationEvent;

class FreeSignUpListener extends Listener
{

    public $GETRequirements = ["clickid", "function"];

    public function dispatch()
    {
        $clickId = UID::decode(NativeRequest::query('clickid'));
        $register = new FreeSignUpRegistrationEvent($clickId);

        return $register->fire();
    }

    public function shouldBeDispatched()
    {
        if ($this->checkGETRequirements()) {
            if (NativeRequest::query('function') == FreeSignUpRegistrationEvent::getEventString()) {
                return true;
            }
        }

        return false;
    }

}
