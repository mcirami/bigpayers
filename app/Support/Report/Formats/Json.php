<?php

namespace App\Support\Report\Formats;


class Json implements Format
{
    public function output($data)
    {
        $json = json_encode($data);

        echo $json;
    }
}
