<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/21/2018
 * Time: 2:24 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents\Listeners;

use LeadMax\TrackYourStats\Clicks\URLEvents\ClickRegistrationEvent;

class ClickListener extends Listener
{

    public $GETRequirements = ["repid", "offerid", "function"];


    public function dispatch()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
            if ( str_contains( $ip, ',' ) ) {
                $ip = substr($ip, 0, strpos($ip, ","));
            }
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if ( str_contains( $ip, ',' ) ) {
                $ip = substr($ip, 0, strpos($ip, ","));
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
            if ( str_contains( $ip, ',' ) ) {
                $ip = substr($ip, 0, strpos($ip, ","));
            }
        }

        $register = new ClickRegistrationEvent($_GET["repid"], $_GET["offerid"], $_GET, $ip);

        return $register->fire();
    }


    public function shouldBeDispatched()
    {
        if ($this->checkGETRequirements()) {
            if ($_GET["function"] == ClickRegistrationEvent::getEventString()) {
                return true;
            }
        }

        return false;
    }

}