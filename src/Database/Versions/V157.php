<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/23/2018
 * Time: 12:03 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V157 extends Version
{

    public function getVersion()
    {
        return 1.57;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE referrals_paid ADD UNIQUE conversion_dupe_check (conversion_id);";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }


    public function verifyUpdate(): bool
    {
        return $this->tableHasIndexes('referrals_paid', ["conversion_dupe_check"]);
    }
}