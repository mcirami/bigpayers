<?php

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V01 extends Version
{


    public function getVersion()
    {
        return 0.1;
    }

    public function update()
    {
        $sql = "ALTER TABLE permissions
                ADD COLUMN create_notifications TINYINT DEFAULT 0;
                ADD COLUMN create_bonuses TINYINT DEFAULT 0;";
        $db = $this->getDB();

        $prep = $db->prepare($sql);


        if ($prep->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function verifyUpdate(): bool
    {
        $sql = "DESCRIBE permissions";
        $prep = $this->getDB()->prepare($sql);
        if ($prep->execute()) {
            $columnNames = $prep->fetchAll(\PDO::FETCH_COLUMN);

            if (is_array($columnNames) &&
                !empty($columnNames) &&
                in_array("create_notifications", $columnNames) &&
                in_array("create_bonuses", $columnNames)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }


    }

}