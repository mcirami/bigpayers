<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/27/2017
 * Time: 11:45 AM
 */

namespace LeadMax\TrackYourStats\Database\Versions;

use LeadMax\TrackYourStats\Database\Version;

class V05 extends Version
{

    public function getVersion()
    {
        return 0.5;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `offer`  ADD `offer_type` TINYINT NOT NULL DEFAULT '0';";
        $prep = $db->prepare($sql);
        if ($prep->execute()) {
            return true;
        } else {
            return false;
        }
    }


    public function verifyUpdate(): bool
    {
        $db = $this->getDB();
        $sql = "DESCRIBE offer";
        $prep = $db->prepare($sql);
        if ($prep->execute()) {
            $columns = $prep->fetchAll(\PDO::FETCH_COLUMN);
            if (is_array($columns) && !empty($columns) && in_array("offer_type", $columns)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}