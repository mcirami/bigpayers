<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/5/2018
 * Time: 10:23 AM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V141 extends Version
{

    public function getVersion()
    {
        return 1.41;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `bonus` ADD `inheritable` TINYINT(1) NOT NULL DEFAULT '0' AFTER `timestamp`;";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableHasColumns('bonus', ["inheritable"]);
    }

}