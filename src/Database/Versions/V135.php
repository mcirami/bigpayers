<?php

namespace LeadMax\TrackYourStats\Database\Versions;

use LeadMax\TrackYourStats\Database\Version;

/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 12/5/2017
 * Time: 3:27 PM
 */
class V135 extends Version
{

    public function getVersion()
    {
        return 1.35;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `offer_caps` ADD `is_capped` TINYINT(2) NOT NULL DEFAULT '0' ;";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableHasColumns('offer_caps', ['is_capped']);
    }

}