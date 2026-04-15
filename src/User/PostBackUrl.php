<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/20/2018
 * Time: 11:47 AM
 */

namespace LeadMax\TrackYourStats\User;


class PostBackUrl
{
    public $user_id;

    public $offer_id;

    const PRIORITY_IGNORE = -1;
    const PRIORITY_LOW = 0;
    const PRIORITY_MEDIUM = 1;
    const PRIORITY_HIGH = 2;

    const GLOBAL_CONVERSION_URL = "url";
    const GLOBAL_FREE_SIGN_UP_URL = "free_sign_up_url";
    const GLOBAL_DEDUCTION_URL = "deduction_url";

    const OFFER_CONVERSION_URL = "postback_url";
    const OFFER_DEDUCTION_URL = "deduction_postback";
    const OFFER_FREE_SIGN_UP_URL = "free_sign_up_postback";

    const CONVERSION_POST_BACK = 0;
    const DEDUCTION_POST_BACK = 1;
    const FREE_SIGN_UP_POST_BACK = 2;

    public $globalURLs = [];

    public $offerURLs = [];

    public $urls = [];


    public function __construct($user_id, $offer_id = false)
    {
        $this->user_id = $user_id;
        $this->getGlobalURLs();

        if ($offer_id) {
            $this->offer_id = $offer_id;
            $this->getOfferUrls();
        }
    }

    public function getOfferPostBackURL($urlName)
    {
        if (isset($this->offerURLs[$urlName])) {
            return $this->offerURLs[$urlName];
        }

        return false;
    }


    public function getGlobalPostBackURL($urlName)
    {
        if (isset($this->globalURLs[$urlName])) {
            return $this->globalURLs[$urlName];
        } else {
            return false;
        }
    }


    private function getOfferUrls()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep_has_offer WHERE rep_idrep = :user_id AND offer_idoffer = :offer_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $this->user_id);
        $prep->bindParam(":offer_id", $this->offer_id);
        $prep->execute();

        $result = $prep->fetch(\PDO::FETCH_OBJ);

        if ($result) {
            foreach ($result as $columnName => $url) {
                if ($columnName !== "idrep_has_offer" && $columnName !== "rep_idrep" && $columnName !== "offer_idoffer" && $columnName !== "payout") {
                    $this->offerURLs[$columnName] = $url;
                }
            }

            return true;
        }

        return false;
    }

    private function getGlobalURLs()
    {
        $userRow = self::querySelectOneUserId($this->user_id)->fetch(\PDO::FETCH_ASSOC);

        if ($userRow == false) {
            return;
        }

        foreach ($userRow as $columnName => $url) {
            if ($columnName !== "id" && $columnName !== "user_id") {
                $this->globalURLs[$columnName] = $url;
            }
        }
    }

    public static function updateUserPostBacks($user_id, $global = "", $deduction = "", $free = "")
    {

        if (self::doesUserHaveTableRow($user_id) == false) {
            self::createUserPostBackRow($user_id);
        }

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE user_postbacks SET url = :global, free_sign_up_url = :free WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);
        $prep->bindParam(":global", $global);
        $prep->bindParam(":free", $free);

        return $prep->execute();
    }

    public static function createUserPostBackRow($user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT IGNORE INTO user_postbacks (user_id) VALUES(:user_id)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);

        return $prep->execute();
    }

    public static function doesUserHaveTableRow($user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM user_postbacks WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);
        $prep->execute();

        if ($prep->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getDeductionURL()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT deduction_url FROM user_postbacks WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $this->user_id);

        $prep->execute();

        $result = $prep->fetch(\PDO::FETCH_OBJ);

        if ($result == false) {
            return "";
        } else {
            return $result->deduction_url;
        }

    }

    public function getFreeSignUpURL()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT free_sign_up_url FROM user_postbacks WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $this->user_id);

        $prep->execute();

        $result = $prep->fetch(\PDO::FETCH_OBJ);

        if ($result == false) {
            return "";
        } else {
            return $result->free_sign_up_url;
        }
    }


    public static function querySelectOneUserId($user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM user_postbacks WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);
        $prep->execute();

        return $prep;
    }

    private function checkURLandAddToList($url, $priority)
    {
        if ($url !== "" && $url !== null) {
            $this->urls[] = ["url" => $url, "priority" => $priority];
        }

    }

    public function getPriorityURL($offer_id = false)
    {
        $highestPriority = self::PRIORITY_IGNORE;
        $currentUrl = "";

        foreach ($this->urls as $url) {
            if ($url["priority"] >= $highestPriority) {
                $highestPriority = $url["priority"];
                $currentUrl = $url["url"];
            }
        }

        return $currentUrl;
    }

    public function updateDeductionURL($url)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE user_postbacks SET deduction_url = :url WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":url", $url);

        return $prep->execute();
    }

    public function updateFreeSignUpURL($url)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE user_postbacks SET free_sign_up_url = :url WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":url", $url);

        return $prep->execute();
    }
}