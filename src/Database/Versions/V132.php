<?php namespace LeadMax\TrackYourStats\Database\Versions;

/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 12/4/2017
 * Time: 4:24 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V132 extends Version
{

    public function getVersion()
    {
        return 1.32;
    }

    public function update()
    {
        $sql = "ALTER TABLE `permissions` ADD `approve_offer_requests` TINYINT NOT NULL DEFAULT '0';
            UPDATE permissions SET approve_offer_requests = 1 WHERE aff_id = 1;
";
        $prep = $this->getDB()->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableHasColumns('permissions', ['approve_offer_requests']);
    }

}