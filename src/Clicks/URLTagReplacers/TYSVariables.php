<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/19/2018
 * Time: 3:39 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLTagReplacers;


use LeadMax\TrackYourStats\Clicks\UID;

class TYSVariables implements TagReplacer
{

    private $user_id;

    private $offer_id;

    private $click_id;

    private $user_name;

    public function __construct($user_id, $user_name, $click_id, $offer_id)
    {
        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $this->click_id = $click_id;
        $this->offer_id = $offer_id;
    }

    public function encodeClickId()
    {
        $this->click_id = UID::encode($this->click_id);
    }

    public function replaceTags($url)
    {
        $url = str_replace("#affid#", $this->user_id, $url);
        $url = str_replace("#offid#", $this->offer_id, $url);
        $url = str_replace("#clickid#", $this->click_id, $url);
        $url = str_replace("#user#", $this->user_name, $url);

        return $url;
    }

}