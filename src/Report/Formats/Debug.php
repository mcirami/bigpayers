<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/28/2017
 * Time: 11:58 AM
 */

namespace LeadMax\TrackYourStats\Report\Formats;


class Debug implements Format
{

    public function output($data)
    {
        var_dump($data);
    }

}