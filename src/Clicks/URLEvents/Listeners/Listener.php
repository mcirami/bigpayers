<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/21/2018
 * Time: 2:19 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents\Listeners;

use function Couchbase\defaultDecoder;

abstract class Listener
{
    protected $GETRequirements = [];

    abstract function shouldBeDispatched();


    abstract function dispatch();


    protected function checkGETRequirements()
    {
        foreach ($this->GETRequirements as $var) {
            if (isset($_GET[$var]) == false || is_null($_GET[$var]) || $_GET[$var] == '') {
                return false;
            }
        }

        return true;
    }


}