<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/26/2018
 * Time: 3:03 PM
 */

namespace LeadMax\TrackYourStats\Offer;


use LeadMax\TrackYourStats\Database\DatabaseConnection;

class CreateOffer
{

    public $idoffer;

    public $created_by;

    public $offer_name;

    public $description;

    public $url;
    public $offer_type;
    public $payout;
    public $status;
    public $offer_timestamp;
    public $visibility;
    public $campaign_id;
    public $parent;


    private function insertIntoOfferTable()
    {
        $db = DatabaseConnection::getInstance();
        $sql =
            "INSERT INTO offer(idoffer,created_by, offer_name, description, url, offer_type, payout, status, offer_timestamp, is_public, campaign_id, parent) VALUES(:idoffer,:created_by,:offer_name,:description,:url,:offer_type,:payout,:status,:offer_timestamp,:is_public,:campaign_id,:parent)";
        $prep = $db->prepare($sql);


        if (isset($this->idoffer)) {
            $prep->bindParam(":idoffer", $this->idoffer);
        } else {
            $prep->bindValue(":idoffer", null);
        }

        $prep->bindParam(":created_by", $this->created_by);
        $prep->bindParam(":offer_name", $this->offer_name);
        $prep->bindParam(":description", $this->description);
        $prep->bindParam(":url", $this->url);
        $prep->bindParam(":offer_type", $this->offer_type);
        $prep->bindParam(":payout", $this->payout);
        $prep->bindParam(":status", $this->status);
        $prep->bindParam(":offer_timestamp", $this->offer_timestamp);
        $prep->bindParam(":is_public", $this->visibility);
        $prep->bindParam(":campaign_id", $this->campaign_id);
        $prep->bindParam(":parent", $this->parent);

        if ($prep->execute()) {
            $this->idoffer = $db->lastInsertId();

            return true;
        } else {
            return false;
        }
    }

    public function save()
    {
        $this->checkAndSetOptionalParams();

        if ($this->insertIntoOfferTable()) {
            return true;
        }

        return false;
    }


    private function checkAndSetOptionalParams()
    {
        if (isset($this->parent) == false) {
            $this->parent = null;
        }

        if (isset($this->offer_timestamp) == false) {
            $this->offer_timestamp = date("Y-m-d H:i:s");
        }

        if (isset($this->status) == false) {
            $this->status = 1;
        }

    }


}