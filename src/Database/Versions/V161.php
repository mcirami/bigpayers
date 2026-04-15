<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 4/9/2018
 * Time: 10:24 AM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V161 extends Version
{

    public function getVersion()
    {
        return 1.61;
    }

    public function update()
    {
        $sql = "
		
			ALTER TABLE rep ADD INDEX status_index (status);

			ALTER TABLE rep ADD INDEX tree_left (lft);

			ALTER TABLE rep ADD INDEX tree_right (rgt);
			
			ALTER TABLE click_bonus ADD INDEX date_index (timestamp);
			
			ALTER TABLE referrals_paid ADD INDEX date_index (timestamp);
		";

        return $this->getDB()->prepare($sql)->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableHasIndexes('rep', ['status_index', 'tree_left', 'tree_right'])
            && $this->tableHasIndexes('click_bonus', ['date_index'])
            && $this->tableHasIndexes('referrals_paid', ['date_index']);
    }

}