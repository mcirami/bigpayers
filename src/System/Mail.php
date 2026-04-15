<?php

namespace LeadMax\TrackYourStats\System;

// mailer class that uses php's dedfault mail function, expect this sets our headers and "from" settings so
// its easier to send mail and not have to find those specific settings

class Mail
{

    public $from = "admin@trafficmasters.com";
    public $to = "";
    public $subject = "";
    public $message = "";

    //dev@trackyourstats.com
    //dev12345

    function __construct($to, $subject, $message)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;

    }


    public function send()
    {
        $headers = 'From: Admin <admin@trafficmasters.com>'."\r\n".
            'X-Mailer: PHP/'.phpversion()."\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


        try {
            mail($this->to, $this->subject, $this->message, $headers);

            return true;
        } catch (\Exception $e) {
            return $e;
        }
    }

}

?>