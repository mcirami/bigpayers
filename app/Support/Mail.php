<?php

namespace App\Support;

class Mail
{
    public $from = 'admin@trafficmasters.com';

    public $to = '';

    public $subject = '';

    public $message = '';

    public function __construct($to, $subject, $message)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
    }

    public function send()
    {
        $headers = "From: Admin <admin@trafficmasters.com>\r\n".
            'X-Mailer: PHP/'.phpversion()."\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        try {
            mail($this->to, $this->subject, $this->message, $headers);

            return true;
        } catch (\Exception $exception) {
            return $exception;
        }
    }
}
