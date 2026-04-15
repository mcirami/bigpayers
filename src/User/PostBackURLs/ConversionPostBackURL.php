<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/1/2018
 * Time: 12:17 PM
 */

namespace LeadMax\TrackYourStats\User\PostBackURLs;


use LeadMax\TrackYourStats\Offer\RepHasOffer;
use LeadMax\TrackYourStats\User\User;

class ConversionPostBackURL extends PostBackURL
{

    public $user_id;

    public $offer_id;

    public $globalPostBackURL;

    public $offerPostBackURL;

    public function __construct($user_id, $offer_id = false)
    {
        $this->user_id = $user_id;
        $this->get_global_post_back();

        if ($offer_id) {
            $this->offer_id = $offer_id;
            $this->get_offer_post_back();
        }
    }

    public function updateGlobalURL($url)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE user_postbacks SET url = :url WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam("url", $url);
        $prep->bindParam(":user_id", $this->user_id);

        return $prep->execute();
    }

    public function updateOfferURL($url)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE rep_has_offer SET postback_url = :url WHERE rep_idrep = :user_id AND offer_idoffer = :offer_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $this->user_id);
        $prep->bindParam(":offer_id", $this->offer_id);
        $prep->bindParam(":url", $url);

        return $prep->execute();
    }

    private function get_global_post_back()
    {
        $this->globalPostBackURL = User::getUsersGlobalPostBackURL($this->user_id);
    }

    private function get_offer_post_back()
    {
        $this->offerPostBackURL = RepHasOffer::getPostbackURL($this->offer_id, $this->user_id);
    }

    public function getPriorityURL()
    {
        if (isset($this->offerPostBackURL) == false || $this->offerPostBackURL == "") {
            if (isset($this->globalPostBackURL)) {
                return $this->globalPostBackURL;
            }
        } else {
            return $this->offerPostBackURL;
        }

        return "";
    }

    public function getGlobalURL()
    {
        return $this->globalPostBackURL;
    }

    public function getOfferSpecificURL()
    {
        return $this->offerPostBackURL;
    }


}