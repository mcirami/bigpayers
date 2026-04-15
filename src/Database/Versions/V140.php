<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 1/11/2018
 * Time: 4:30 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V140 extends Version
{

    public function getVersion()
    {
        return 1.40;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `rep` ADD `company_name` VARCHAR(255) NOT NULL;";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableHasColumns('rep', ['company_name']);
    }

}