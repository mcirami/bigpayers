<?php

namespace App\Exceptions\RegistrationEventExceptions;

use Exception;
use LeadMax\TrackYourStats\Clicks\UID;
use Throwable;

class ClickConvertedException extends Exception
{

    public function __construct($clickId = false)
    {
        $message = 'Click is already converted.';
        $code = 0;
        $previous = null;

        parent::__construct($message, $code, $previous);
    }
}
