<?php

namespace LeadMax\TrackYourStats\Clicks;


use LeadMax\TrackYourStats\Database\DatabaseConnection;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Bonus;
use LeadMax\TrackYourStats\User\ReferralRegister;
use LeadMax\TrackYourStats\User\Referrals;
use \LeadMax\TrackYourStats\User\User;
use PDO;


class Conversion
{

    public $id;
    public $click_id;
    public $user_id;
    public $paid;
    public $timestamp;

    public $clickData;

    public function __construct($clickId = false)
    {
        if ($clickId) {
            $this->setClickId($clickId);
        }
    }

    public static function doesUserOwnConversion($user_id, $conversion_id)
    {
        $conversion = self::selectOneByConversionID($conversion_id)->fetch(PDO::FETCH_OBJ);
        if ($conversion == false) {
            return false;
        }


        return ($conversion->user_id == $user_id);
    }

    public function setClickId($clickId)
    {
        $this->click_id = $clickId;
        $this->getClickData();
    }

    public static function doesLoggedInUserOwnConversion($conversionId)
    {
        switch (Session::userType()) {
            case \App\Privilege::ROLE_GOD:
                return true;

            case \App\Privilege::ROLE_ADMIN:
            case \App\Privilege::ROLE_MANAGER:
                return self::doesManagerOwnConversion(Session::userID(), $conversionId);

            case \App\Privilege::ROLE_AFFILIATE:
                return self::doesUserOwnConversion(Session::userID(), $conversionId);

            default:
                return false;
        }
    }

    public static function doesManagerOwnConversion($user_id, $conversion_id)
    {
        $conversion = self::selectOneByConversionID($conversion_id)->fetch(PDO::FETCH_OBJ);
        if ($conversion == false) {
            return false;
        }

        return (User::userOwnsUser($user_id, $conversion_id->user_id));
    }

    public function registerSale()
    {
        $this->checkAndSetTimestamp();
        $this->getAffiliateData();
        if (is_null($this->clickData)) {
            $this->getClickData();
        }
        if ($this->save()) {
            //TODO: This could be moved to a ConversionObserver
            $this->checkAndUpdateClickTypeIfBlacklisted();
            $this->checkAndRegisterReferralCommission();
            $this->checkAndRegisterBonuses();

            return true;
        } else {
            return false;
        }
    }

    private function checkAndRegisterReferralCommission()
    {
        $referral = new ReferralRegister($this->user_id);
        $referral->registerCommission($this->id);
    }

    private function checkAndUpdateClickTypeIfBlacklisted()
    {
        if ($this->clickData->click_type == Click::TYPE_BLACKLISTED) {
            Click::updateClickType($this->click_id, Click::TYPE_RAW);
        }
    }

    public function save()
    {
        $db = DatabaseConnection::getInstance();
        $sql = "INSERT INTO conversions (user_id, click_id, timestamp, paid) VALUES (:user_id, :clickID, :timestamp, :paid)";

        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $this->user_id);
        $prep->bindParam(":clickID", $this->click_id);
        $prep->bindParam(":paid", $this->paid, PDO::PARAM_INT);
        $prep->bindParam(":timestamp", $this->timestamp);


        if ($prep->execute()) {
            $this->id = $db->lastInsertId();

            return true;
        } else {
            return false;
        }
    }

    private function checkAndSetTimestamp()
    {
        if (!isset($this->timestamp)) {
            $this->timestamp = date("Y-m-d H:i:s");
        }
    }

    public function getAffiliateData()
    {
        $db = DatabaseConnection::getInstance();
        $sql = "SELECT clicks.rep_idrep, rep_has_offer.payout FROM clicks INNER JOIN rep_has_offer ON rep_has_offer.rep_idrep = clicks.rep_idrep AND rep_has_offer.offer_idoffer = clicks.offer_idoffer WHERE idclicks = :click_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":click_id", $this->click_id);
        $prep->execute();
        $result = $prep->fetch(PDO::FETCH_ASSOC);

        if ($result == false) {
            return false;
        }

        $this->user_id = $result["rep_idrep"];

        if ($this->customPayoutSet() == false) {
            $this->paid = $result["payout"];
        }

        return true;
    }

    private function customPayoutSet()
    {
        return isset($this->paid);
    }


    public function getClickData()
    {
        $click = Click::SelectOne($this->click_id);

        $this->clickData = $click;
    }

    public function isValidClick()
    {
        if (!$this->clickData) {
            return false;
        } else {
            return true;
        }
    }

    public static function isClickConverted($clickId)
    {
        return static::selectOne($clickId)->rowCount() > 0;
    }

    private function checkAndRegisterBonuses()
    {
        // check bonuses for affiliate with this click id
        $bonus = new Bonus($this->user_id);
        $bonus->processAll();
    }

    static function selectOne($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM conversions WHERE click_id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);
        $prep->execute();

        return $prep;

    }

    static function selectOneByConversionID($ConversionId)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM conversions WHERE id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $ConversionId);
        $prep->execute();

        return $prep;

    }

    // OLD AND DISGUSTING
    static function Conversion($clickid, $customPayout = false, $returnConversionId = false)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        $click = Click::SelectOne($clickid);

        if ($click == false) {
            return false;
        }

        // check if this click already has a conversion
        if (static::selectOne($clickid)->fetch(PDO::FETCH_NUM) !== false) {
            return false;
        }


        $sql = "SELECT clicks.rep_idrep, rep_has_offer.payout FROM clicks INNER JOIN rep_has_offer ON rep_has_offer.rep_idrep = clicks.rep_idrep AND rep_has_offer.offer_idoffer = clicks.offer_idoffer WHERE idclicks = :click_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":click_id", $clickid);
        $prep->execute();
        $result = $prep->fetch(PDO::FETCH_ASSOC);

        $user_id = $result["rep_idrep"];
        $paid = $result["payout"];

        if ($customPayout) {
            $paid = $customPayout;
        }


        $sql = "INSERT INTO conversions (user_id, click_id, timestamp, paid) VALUES (:user_id, :clickID, :timestamp, :paid)";

        $timestamp = date("Y-m-d H:i:s");
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);
        $prep->bindParam(":clickID", $clickid);
        $prep->bindParam(":paid", $paid);
        $prep->bindParam(":timestamp", $timestamp);


        $result = $prep->execute();

        if ($result) {
            if ($click->click_type == Click::TYPE_BLACKLISTED) {
                Click::updateClickType($clickid, Click::TYPE_RAW);
            }
        }

        $referral = new Referrals($user_id, true);
        $referral->logRegistrationEvent($db->lastInsertId(), $user_id);
        $referral->registerCommission($paid);

        return $result;
    }


}