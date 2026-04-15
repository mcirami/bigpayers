<?php namespace LeadMax\TrackYourStats\Report\Formats;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/23/2017
 * Time: 11:32 AM
 */

interface Format
{
    public function output($report);
}