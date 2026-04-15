<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/19/2018
 * Time: 3:47 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLTagReplacers;


class Base64 implements TagReplacer
{

    public function replaceTags($url)
    {
        $start = strpos($url, "<base64>");
        $end = strpos($url, "</base64>");


        if ($start !== false && $end !== false) {
            $urlStartBeforeTag = substr($url, 0, $start);
            $urlAfterTag = substr($url, $end + 9);

            $removedFirstTag = substr($url, strpos($url, "<base64>") + 8);
            $encodeInside = substr($removedFirstTag, 0, strpos($removedFirstTag, "</base64>"));

            $encodeInside = base64_encode($encodeInside);


            $newURL = $urlStartBeforeTag.$encodeInside.$urlAfterTag;

            // loops through and checks for anymore tags
            return self::replaceTags($newURL);
        } else {
            return $url;
        }
    }

}