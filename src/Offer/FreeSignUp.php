<?php

namespace LeadMax\TrackYourStats\Offer;


use LeadMax\TrackYourStats\Clicks\Click;

class FreeSignUp
{

    private $id;
    private $click_id;
    private $user_id;
    private $timestamp;

    public function __construct()
    {

    }


    public static function selectOne($click_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM free_sign_ups WHERE click_id = :click_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":click_id", $click_id);
        $prep->execute();


        return $prep->fetch(\PDO::FETCH_OBJ);
    }

    public function getUserIdFromClickId()
    {
        $click = Click::SelectOne($this->click_id);

        $this->user_id = $click->rep_idrep;
    }

    public static function createFromId($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM free_sign_ups WHERE id =:id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);
        if ($prep->execute()) {
            $signup = $prep->fetch(\PDO::FETCH_OBJ);
            if ($signup == false) {
                return false;
            }

            $obj = new FreeSignUp();
            $obj->id = $signup->id;
            $obj->click_id = $signup->click_id;
            $obj->user_id = $signup->user_id;
            $obj->timestamp = $signup->timestamp;

            return $obj;
        } else {
            return false;
        }

    }

    public function setClickId($click_id)
    {
        $this->click_id = $click_id;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function getClickId()
    {
        return $this->click_id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }


    public function save()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT IGNORE INTO free_sign_ups (click_id, user_id) VALUES(:click_id, :user_id)";
        $prep = $db->prepare($sql);

        $prep->bindParam(":click_id", $this->click_id);
        $prep->bindParam(":user_id", $this->user_id);

        if ($prep->execute()) {
            $this->id = $db->lastInsertId();

            return true;
        } else {
            return false;
        }

    }


    public function update()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE free_sign_ups SET click_id = :click_id, user_id = :user_id, timestamp = :timestamp WHERE id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $this->id);
        $prep->bindParam(":click_id", $this->click_id);
        $prep->bindParam(":user_id", $this->user_id);
        $prep->bindParam(":timestamp", $this->timestamp);

        return $prep->execute();
    }

}