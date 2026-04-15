<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 1/9/2018
 * Time: 11:14 AM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V138 extends Version
{


    public function getVersion()
    {
        return 1.38;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE clicks DROP INDEX `main`; ADD INDEX `v2` (`rep_idrep`, `offer_idoffer`, `click_type`, `first_timestamp`) USING BTREE;";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        if ($this->tableHasIndexes('clicks', ['main']) == true) {
            return false;
        }

        if ($this->tableHasIndexes('clicks', ['v2']) == true) {
            return true;
        }

        return false;
    }

}