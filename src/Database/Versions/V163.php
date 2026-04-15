<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 4/16/2018
 * Time: 11:53 AM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V163 extends Version
{

    public function getVersion()
    {
        return 1.63;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `company` ADD `db_version` DOUBLE NOT NULL AFTER `uid`;";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableHasColumns('company', ['db_version']);
    }

}