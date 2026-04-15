<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/1/2018
 * Time: 12:23 PM
 */

namespace LeadMax\TrackYourStats\User\PostBackURLs;


class DeductionPostBackURL extends PostBackURL
{

    public $user_id;

    public $offer_id;

    public $globalPostBack;

    public $offerPostBack;

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
        $sql = "UPDATE user_postbacks SET deduction_url = :url WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam("url", $url);
        $prep->bindParam(":user_id", $this->user_id);

        return $prep->execute();
    }

    public function updateOfferURL($url)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE rep_has_offer SET deduction_postback = :url WHERE rep_idrep = :user_id AND offer_idoffer = :offer_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $this->user_id);
        $prep->bindParam(":offer_id", $this->offer_id);
        $prep->bindParam(":url", $url);

        return $prep->execute();
    }

    private function get_offer_post_back()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT deduction_postback FROM rep_has_offer WHERE rep_idrep = :user_id AND offer_idoffer = :offer_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $this->user_id);
        $prep->bindParam(":offer_id", $this->offer_id);

        $prep->execute();

        $result = $prep->fetch(\PDO::FETCH_OBJ);

        if ($result == false) {
            $this->globalPostBack = "";

            return;
        }

        $this->offerPostBack = $result->deduction_postback;
    }

    private function get_global_post_back()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT deduction_url FROM user_postbacks WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $this->user_id);

        $prep->execute();

        $result = $prep->fetch(\PDO::FETCH_OBJ);

        if ($result == false) {
            $this->globalPostBack = "";
        } else {
            $this->globalPostBack = $result->deduction_url;
        }

    }


    public function getPriorityURL()
    {
        if ($this->offerPostBack == "") {
            return $this->globalPostBack;
        } else {
            return $this->offerPostBack;
        }

    }

    public function getGlobalURL()
    {
        return $this->globalPostBack;
    }

    public function getOfferSpecificURL()
    {
        return $this->offerPostBack;
    }

}