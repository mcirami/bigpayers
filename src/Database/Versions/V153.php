<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * <<<<<<< HEAD
 * Date: 3/20/2018
 * Time: 1:10 PM
 * =======
 * <<<<<<< HEAD
 * Date: 3/8/2018
 * Time: 11:10 AM
 * =======
 * Date: 3/6/2018
 * Time: 2:24 PM
 * >>>>>>> Added new Permission sale_logs, stillneeds to be added to pages required. Manager reports for sale log still need to be added. Deleted testing files.
 * >>>>>>> salelog
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V153 extends Version
{

    public function getVersion()
    {
        return 1.53;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "
CREATE TABLE `sale_log` (
  `id` INT(11) NOT NULL,
  `conversion_id` INT(11) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `sale_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `conversion_dupe_check` (`conversion_id`),
  ADD KEY `conver_id` (`conversion_id`);

ALTER TABLE `sale_log`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `sale_log`
  ADD CONSTRAINT `sale_log_ibfk_1` FOREIGN KEY (`conversion_id`) REFERENCES `conversions` (`id`);

		";

        $prep = $db->prepare($sql);

        return $prep->execute();
    }


    public function verifyUpdate(): bool
    {
        return $this->tableExists('sale_log');
    }

}