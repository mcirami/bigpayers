<?php

namespace App\Exceptions\RegistrationEventExceptions;

use Exception;

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
