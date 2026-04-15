<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/19/2018
 * Time: 3:06 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLTagReplacers;

interface TagReplacer
{
    public function replaceTags($url);

}