<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * <<<<<<< HEAD
 * Date: 3/21/2018
 * Time: 10:32 AM
 * =======
 * Date: 3/8/2018
 * Time: 11:12 AM
 * >>>>>>> salelog
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V154 extends Version
{

    public function getVersion()
    {
        return 1.54;
    }


    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `permissions` ADD `sale_logs` TINYINT(1) NOT NULL DEFAULT '0' AFTER `adjust_sales`;
				  UPDATE permissions SET sale_logs = 1 WHERE aff_id = 1; ";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableHasColumns('permissions', ['sale_logs']);
    }

}