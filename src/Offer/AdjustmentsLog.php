<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/27/2018
 * Time: 10:29 AM
 */

namespace LeadMax\TrackYourStats\Offer;


class AdjustmentsLog
{

    const ACTION_CREATE_SALE = 0;
    const ACTION_DEDUCT_SALE = 1;
    const ACTION_EDIT_SALE = 2;

    public $conversion_id;

    public $user_id;

    public $action;

    public function __construct($conversion_id, $user_id)
    {
        $this->conversion_id = $conversion_id;
        $this->user_id = $user_id;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function log()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO adjustments_log (conversion_id, user_id, action) VALUES(:conversion_id, :user_id, :action)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":conversion_id", $this->conversion_id);
        $prep->bindParam(":user_id", $this->user_id);
        $prep->bindParam(":action", $this->action);

        return $prep->execute();
    }


}