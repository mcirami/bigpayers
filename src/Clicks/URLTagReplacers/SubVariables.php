<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/19/2018
 * Time: 3:07 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLTagReplacers;


class SubVariables implements TagReplacer
{


    private $subVars = [
        "sub1" => "",
        "sub2" => "",
        "sub3" => "",
        "sub4" => "",
        "sub5" => "",
    ];

    public function __construct(array $subVars)
    {
        $this->subVars = $subVars;
    }

    public function replaceTags($url)
    {
        for ($i = 1; $i <= 5; $i++) {
            if (isset($this->subVars["sub{$i}"])) {
                $url = str_replace("#sub{$i}#", $this->subVars["sub{$i}"], $url);
            } else {
                // if its not found, strip tag
                $url = str_replace("#sub{$i}#", "", $url);
            }
        }

        return $url;
    }


    public function getSubVarsFromDataBase($click_id)
    {
    }


}