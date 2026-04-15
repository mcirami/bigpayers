<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/29/2017
 * Time: 11:36 AM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V131 extends Version
{
    public function getVersion()
    {
        return 1.31;
    }

    public function update()
    {
        $sql = "ALTER TABLE `clicks` ADD INDEX `date1` (`first_timestamp`); 
ALTER TABLE `clicks` ADD INDEX `date2` (`first_timestamp`); 
ALTER TABLE `conversions` ADD INDEX `date` (`timestamp`); 


              ";

        $prep = $this->getDB()->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {

        if ($this->tableHasIndexes('clicks', ['date1', 'date2']) && $this->tableHasIndexes('conversions', ['date'])) {
            return true;
        } else {
            return false;
        }


    }

}