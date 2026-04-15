<?php

namespace App\Exceptions\RegistrationEventExceptions;

use Exception;

class ConversionAlreadyPendingException extends Exception
{
    protected $message = 'Conversion is already pending.';
}
