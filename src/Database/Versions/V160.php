<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 4/6/2018
 * Time: 2:40 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V160 extends Version
{

    public function getVersion()
    {
        return 1.60;
    }


    public function update()
    {
        $sql = "ALTER TABLE offer ADD INDEX status_index (status);
		";

        return $this->getDB()->prepare($sql)->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableHasIndexes('offer', ['status_index']);
    }

}