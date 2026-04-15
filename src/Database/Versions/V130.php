<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/27/2017
 * Time: 1:20 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V130 extends Version
{

    public function getVersion()
    {
        return 1.30;
    }


    private function addSubColumns()
    {
        $sql = "ALTER TABLE `click_vars` ADD `sub1` VARCHAR(255) NOT NULL DEFAULT '' AFTER `url`,
ADD `sub2` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sub1`,
ADD `sub3` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sub2`, 
ADD `sub4` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sub3`,
ADD `sub5` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sub4`;";
        $prep = $this->getDB()->prepare($sql);
        if ($prep->execute()) {
            return true;
        } else {
            return false;
        }
    }

    private function fillSubColumns()
    {
        $sql = "SELECT * FROM click_vars";
        $prep = $this->getDB()->prepare($sql);
        $prep->execute();
        $result = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $sql = "UPDATE click_vars ";

        foreach ($result as $row) {
            $subs = ClickVars::processUrlToSubIDArray($row['url']);

            //build query
            $sql = "UPDATE click_vars SET ";
            $newSubs = array();

            $i = 0;
            foreach ($subs as $sub => $val) {
                if ($val !== "" && $val !== null) {

                    $newSubs[$i] = array();
                    $newSubs[$i]["sub"] = $sub;
                    $newSubs[$i]["val"] = $val;
                    $i++;
                }

            }

            if (!empty($newSubs)) {

                $sql = "UPDATE click_vars SET";
                $insertValues = [];
                for ($i = 0; $i < count($newSubs); $i++) {
                    if ($i !== count($newSubs) - 1 && count($newSubs) !== 1) {
                        $sql .= " {$newSubs[$i]["sub"]} = ?, ";
                    } else {
                        $sql .= " {$newSubs[$i]["sub"]} = ? ";
                    }

                    $insertValues[] = $newSubs[$i]["val"];

                }

                $insertValues[] = $row["click_id"];

                $sql .= "WHERE click_id = ?";


                $prep = $this->getDB()->prepare($sql);

                if (!$prep->execute($insertValues)) {
                    return false;
                }

            }


        }

        return true;

    }


    public function update()
    {
        $this->beginTransaction();
        if ($this->addSubColumns()) {
            $this->commit();

            return true;
        } else {
            $this->rollBack();

            return false;
        }
    }

    public
    function verifyUpdate(): bool
    {
        $db = $this->getDB();
        $sql = "DESCRIBE click_vars";
        $prep = $db->prepare($sql);
        if ($prep->execute()) {
            $columns = $prep->fetchAll(\PDO::FETCH_COLUMN);
            if (is_array($columns) && !empty($columns)) {
                for ($i = 1; $i <= 5; $i++) {
                    if (!in_array("sub{$i}", $columns)) {
                        return false;
                    }
                }

                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

}