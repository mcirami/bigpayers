<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 12/7/2017
 * Time: 4:23 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V133 extends Version
{

    public function getVersion()
    {
        return 1.33;
    }

    public function update()
    {
        $sql = "ALTER TABLE clicks DROP COLUMN conversion, DROP COLUMN conversion_timestamp";

        $prep = $this->getDB()->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {

        try {
            if ($this->tableHasColumns('clicks', ['conversion', 'conversion_timestamp']) == true) {
                return false;
            } else {
                return true;
            }

        } catch (\Exception $e) {
            die($e);
        }
    }
}