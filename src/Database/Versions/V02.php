<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/27/2017
 * Time: 11:31 AM
 */

namespace LeadMax\TrackYourStats\Database\Versions;

use LeadMax\TrackYourStats\Database\Version;

class V02 extends Version
{
    public function getVersion()
    {
        return 0.2;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE permissions
                ADD COLUMN assign_bonuses TINYINT DEFAULT 0;";
        $prep = $db->prepare($sql);
        if ($prep->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function verifyUpdate(): bool
    {
        $db = $this->getDB();
        $sql = "DESCRIBE permissions";
        $prep = $db->prepare($sql);

        if ($prep->execute()) {
            $columns = $prep->fetchAll(\PDO::FETCH_COLUMN);
            if (is_array($columns) && !empty($columns)) {
                if (in_array("assign_bonuses", $columns)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }
}