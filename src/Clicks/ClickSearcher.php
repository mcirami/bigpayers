<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/26/2018
 * Time: 11:04 AM
 */


namespace LeadMax\TrackYourStats\Clicks;

use App\Support\LegacyDatabaseConnection as DatabaseConnection;

class ClickSearcher
{


    public $clickId;

    public function __construct($clickId)
    {
        $this->clickId = $clickId;
    }


    public function clickVars()
    {
        $db = DatabaseConnection::getInstance();
        $sql = "SELECT * FROM click_vars WHERE click_id = :click_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":click_id", $this->clickId);
        $prep->execute();

        return $prep;
    }

    public function clickData()
    {
        $db = DatabaseConnection::getInstance();
        $sql = "SELECT * FROM clicks WHERE idclicks = :click_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":click_id", $this->clickId);
        $prep->execute();

        return $prep;
    }


}
