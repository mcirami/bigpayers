<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/21/2018
 * Time: 10:34 AM
 */

namespace LeadMax\TrackYourStats\Offer\Rules\Handlers;


class NoneUnique
{
    private $idrule;

    public $name;

    public $offer_idoffer;

    public $type = "none_unique";

    public $redirect_offer;

    public $is_active;

    public $deny = 0;

    public function __construct()
    {
    }

    public function getRuleId()
    {
        return $this->idrule;
    }

    public function save()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO rule(name, offer_idoffer, type, redirect_offer, is_active, deny) VALUES (:name, :offer_id, :type, :redirect_offer, :is_active, :deny)";
        $prep = $db->prepare($sql);

        $prep->bindParam(":name", $this->name);
        $prep->bindParam(":offer_id", $this->offer_idoffer);
        $prep->bindParam(":type", $this->type);
        $prep->bindParam(":redirect_offer", $this->redirect_offer);
        $prep->bindParam(":is_active", $this->is_active);
        $prep->bindParam(":deny", $this->deny);

        return $prep->execute();
    }

    public function update()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE rule SET name = :name, offer_idoffer = :offer_id, type = :type, redirect_offer = :redirect_offer,  is_active = :is_active, deny = :deny WHERE idrule = :id";
        $prep = $db->prepare($sql);

        $prep->bindParam(":name", $this->name);
        $prep->bindParam(":offer_id", $this->offer_idoffer);
        $prep->bindParam(":type", $this->type);
        $prep->bindParam(":redirect_offer", $this->redirect_offer);
        $prep->bindParam(":is_active", $this->is_active);
        $prep->bindParam(":deny", $this->deny);
        $prep->bindParam(":id", $this->idrule);

        return $prep->execute();
    }


    public static function loadFromId($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rule WHERE idrule = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);

        $prep->execute();

        $result = $prep->fetch(\PDO::FETCH_OBJ);

        $rule = new NoneUnique();
        $rule->idrule = $id;
        $rule->name = $result->name;
        $rule->offer_idoffer = $result->offer_idoffer;
        $rule->type = $result->type;
        $rule->redirect_offer = $result->redirect_offer;
        $rule->is_active = $result->is_active;
        $rule->deny = $result->deny;

        return $rule;
    }

}