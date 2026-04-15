<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 1/11/2018
 * Time: 12:42 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V139 extends Version
{

    public function getVersion()
    {
        return 1.39;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `permissions` ADD `approve_affiliate_sign_ups` TINYINT(2) NOT NULL DEFAULT '0';
			UPDATE permissions SET approve_affiliate_sign_ups = 1 WHERE aff_id = 1;
";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        if ($this->tableHasColumns('permissions', ['approve_affiliate_sign_ups'])) {
            return true;
        } else {
            return false;
        }
    }

}