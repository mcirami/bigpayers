<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/22/2018
 * Time: 12:59 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V146 extends Version
{

    public function getVersion()
    {
        return 1.46;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "CREATE TABLE `deductions` (
  `id` int(11) NOT NULL,
  `conversion_id` int(10) UNSIGNED NOT NULL,
  `deduction_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `deductions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conversion_index` (`conversion_id`);


ALTER TABLE `deductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  ALTER TABLE `deductions` CHANGE `conversion_id` `conversion_id` INT(11) NOT NULL;
  ALTER TABLE deductions ADD FOREIGN KEY (conversion_id) REFERENCES  conversions(id);
		";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableExists('deductions');
    }
}