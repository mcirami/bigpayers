<?php

namespace App\Exceptions\RegistrationEventExceptions;

use Exception;
use LeadMax\TrackYourStats\Clicks\UID;
use Throwable;

class InvalidClickException extends Exception
{

    public function __construct($clickId = false)
    {
        $message = 'Invalid Click ID';
        $code = 0;
        $previous = null;
        parent::__construct($message, $code, $previous);
    }

}
