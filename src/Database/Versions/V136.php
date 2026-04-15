<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 12/13/2017
 * Time: 5:26 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V136 extends Version
{

    public function getVersion()
    {
        return 1.36;
    }

    public function update()
    {
        $sql = "

ALTER TABLE `offer` ADD `parent` INT(10) UNSIGNED NULL DEFAULT NULL;
        

ALTER TABLE offer
ADD CONSTRAINT fk_parent
FOREIGN KEY (parent) REFERENCES offer(idoffer);


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
                $this->tableHasIndexes('offer', ['fk_parent'])
            );
        } catch (\Exception $e) {
            die($e);
        }
    }

}