<?php

namespace LeadMax\TrackYourStats\Database\Versions;

use LeadMax\TrackYourStats\Database\Version;

/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 12/5/2017
 * Time: 3:27 PM
 */
class V134 extends Version
{

    public function getVersion()
    {
        return 1.34;
    }

    public function update()
    {
        $sql = "

ALTER TABLE `offer` ADD `parent` INT(10) UNSIGNED NULL DEFAULT NULL;
        
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE offer
ADD CONSTRAINT fk_parent
FOREIGN KEY (parent) REFERENCES offer(idoffer);

SET FOREIGN_KEY_CHECKS = 1;

";

        $prep = $this->getDB()->prepare($sql);

        return $prep->execute();
    }


    public function verifyUpdate(): bool
    {
        try {
            return (
                $this->tableHasColumns('offer', ['parent'])
                &&
                $this->tableHasIndexes('offer', ['parent'])
            );
        } catch (\Exception $e) {
            die($e);
        }
    }
}