<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/19/2018
 * Time: 1:35 PM
 */

namespace LeadMax\TrackYourStats\Clicks;


use LeadMax\TrackYourStats\Clicks\URLTagReplacers\TagReplacer;

class URLProcessor
{


    public $url;

    private $replaces = [];

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function addTagReplacer(TagReplacer $replacer)
    {
        $this->replaces[] = $replacer;
    }


    public function processURL()
    {
        foreach ($this->replaces as $replacer) {
            $this->url = $replacer->replaceTags($this->url);
        }
    }

    public function sendUserToUrl()
    {
        send_to($this->url);
    }

    public function curlURL($dumpResult = false)
    {
        $ch = curl_init();


        // set url
        curl_setopt($ch, CURLOPT_URL, $this->url);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        if ($dumpResult) {
            dd($output);
        }
    }

    public function dumpURLAndDie()
    {
        var_dump($this->url);
        die();
    }

}