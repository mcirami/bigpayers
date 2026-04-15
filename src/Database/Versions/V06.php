<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/27/2017
 * Time: 11:49 AM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V06 extends Version
{

    public function getVersion()
    {
        return 0.6;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "

SET FOREIGN_KEY_CHECKS = 0;
drop table if exists conversions;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `conversions` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `click_id` int(10) UNSIGNED NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `conversions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `click_id` (`click_id`);

ALTER TABLE `conversions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `conversions`
  ADD CONSTRAINT `conversions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`),
  ADD CONSTRAINT `conversions_ibfk_2` FOREIGN KEY (`click_id`) REFERENCES `clicks` (`idclicks`);
";

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
        $sql = "DESCRIBE conversions";
        $prep = $db->prepare($sql);
        if ($prep->execute()) {
            $columns = $prep->fetchAll(\PDO::FETCH_COLUMN);
            if (is_array($columns) && !empty($columns)) {
                return $this->hasColumns($columns);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function hasColumns($array)
    {
        $columns = ['id', 'user_id', 'click_id', 'timestamp', 'paid'];

        foreach ($columns as $column) {
            if (!in_array($column, $array)) {
                return false;
            }
        }

        return true;
    }

}