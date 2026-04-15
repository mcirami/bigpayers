<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 1/3/2018
 * Time: 4:48 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;
use function System\dd;

class V137 extends Version
{

    public function getVersion()
    {
        return 1.37;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `log` CHANGE `description` `description` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";

        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        $db = $this->getDB();
        $sql = "SHOW COLUMNS FROM log";
        $prep = $db->prepare($sql);
        if ($prep->execute()) {

            $result = $prep->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $column) {
                if ($column["Field"] == 'description') {
                    if ($column["Type"] == "varchar(255)") {
                        return true;
                    } else {
                        return false;
                    }
                }

            }

            return false;

        } else {
            return false;
        }
    }

}