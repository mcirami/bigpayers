<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/16/2018
 * Time: 10:53 AM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V143 extends Version
{

    public function getVersion()
    {
        return 1.43;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `permissions` ADD `edit_affiliates` TINYINT(1) NOT NULL DEFAULT '0'; UPDATE permissions SET edit_affiliates = 1 WHERE aff_id = 1;";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        if ($this->tableHasColumns('permissions', ['edit_affiliates'])) {
            return true;
        } else {
            return false;
        }
    }

}