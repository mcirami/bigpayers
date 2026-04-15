<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/26/2018
 * Time: 4:47 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V149 extends Version
{


    public function getVersion()
    {
        return 1.49;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `permissions` ADD `adjust_sales` TINYINT(1) NOT NULL DEFAULT '0' AFTER `edit_report_permissions`;
				UPDATE permissions SET adjust_sales = 1 WHERE aff_id = 1;
";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableHasColumns('permissions', ['adjust_sales']);
    }


}