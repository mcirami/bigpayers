<?php

namespace LeadMax\TrackYourStats\Report\Formats;


class JSON implements Format
{
    public function output($data)
    {
        $json = json_encode($data);

        echo $json;
    }
}