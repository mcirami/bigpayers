<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/23/2018
 * Time: 12:06 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V156 extends Version
{


    public function getVersion()
    {
        return 1.56;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `referrals_paid` ADD `conversion_id` INT NULL DEFAULT NULL AFTER `paid`;
				ALTER TABLE referrals_paid ADD FOREIGN KEY (conversion_id) REFERENCES conversions(id); ";

        $prep = $db->prepare($sql);

        return $prep->execute();
    }


    public function verifyUpdate(): bool
    {
        return $this->tableHasColumns('referrals_paid', ["conversion_id"]);
    }

}

