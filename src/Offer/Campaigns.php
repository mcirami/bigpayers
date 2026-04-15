<?php

namespace LeadMax\TrackYourStats\Offer;

use LeadMax\TrackYourStats\System\Session;
use PDO;

class Campaigns
{


    public $userType = \App\Privilege::ROLE_UNKNOWN;

    function __construct($userType)
    {
        $this->userType = $userType;
    }


    public static function getDefaultCampaignId()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT id FROM campaigns ORDER BY id ASC LIMIT 1";
        $prep = $db->prepare($sql);
        $prep->execute();

        return $prep->fetch(PDO::FETCH_ASSOC)["id"];
    }

    public function selectCampaigns()
    {
        if ($this->userType == \App\Privilege::ROLE_GOD) {
            $sql = "SELECT * FROM campaigns";
        } else {
            return $this->selectCampaignsWithOwnedOffersOnly();
        }

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        $prep = $db->prepare($sql);

        $prep->execute();

        return $prep;
    }

    public static function selectCampaign($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM campaigns WHERE id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);
        $prep->execute();

        return $prep;
    }


    public static function selectCampaignOffers($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM offer WHERE campaign_id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);
        $prep->execute();

        return $prep;
    }


    public function selectCampaignsWithOwnedOffersOnly()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT offer_idoffer FROM rep_has_offer INNER JOIN rep ON rep.lft > :left AND rep.rgt < :right GROUP BY offer_idoffer";
        $prep = $db->prepare($sql);
        $left = Session::userData()->lft;
        $right = Session::userData()->rgt;
        $prep->bindParam(":left", $left);
        $prep->bindParam(":right", $right);
        $prep->execute();

        $ownedOfferIDs = $prep->fetchAll(PDO::FETCH_OBJ);
        $offerIds = [];
        foreach ($ownedOfferIDs as $offer) {
            $offerIds[] = $offer->offer_idoffer;
        }


        $sql = "SELECT * FROM campaigns LEFT JOIN offer ON offer.campaign_id = campaigns.id AND offer.idoffer IN (";

        for ($i = 0; $i < count($offerIds); $i++) {
            if ($i != count($offerIds) - 1) {
                $sql .= "?,";
            } else {
                $sql .= "?)";
            }
        }

        $sql .= " GROUP BY id";

        $prep = $db->prepare($sql);

        $prep->execute($offerIds);

        return $prep;
    }

    public static function createCampaign($name)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO campaigns (name, timestamp) VALUES(:name, :timestamp)";
        $prep = $db->prepare($sql);

        $timestamp = date("U");

        $prep->bindParam(":name", $name);
        $prep->bindParam(":timestamp", $timestamp);

        if ($prep->execute()) {
            return $db->lastInsertId();
        } else {
            return false;
        }
    }

    public static function updateCampaign($id, $name)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE campaigns SET name = :name WHERE id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);
        $prep->bindParam(":name", $name);

        return $prep->execute();
    }


    public static function assignOffer($campaignID, $offerID)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE offer SET campaign_id = :campaign_id WHERE idoffer = :offer_id";
        $prep = $db->prepare($sql);

        $prep->bindParam(":campaign_id", $campaignID);
        $prep->bindParam(":offer_id", $offerID);

        return $prep->execute();
    }


    public static function assignOffers($campaignID, $offerList)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE offer SET campaign_id = ? WHERE idoffer IN (";

        $insertValues = [$campaignID];

        for ($i = 0; $i < count($offerList); $i++) {
            if ($i !== count($offerList)) {
                $sql .= "?,";
            } else {
                $sql .= "?)";
            }
            $insertValues[] = $offerList[$i];
        }

        $prep = $db->prepare($sql);

        return $prep->execute($insertValues);
    }


    public static function removeOffers($campaign_id, $offerList)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

    }


    public function queryGetOffers()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        if ($this->userType !== \App\Privilege::ROLE_GOD) {
            return Offer::selectOwnedOffers($this->userType);
        } else {
            $sql = "SELECT * FROM offer ORDER BY idoffer DESC";
        }
        $prep = $db->prepare($sql);
        $prep->execute();

        return $prep;
    }


}