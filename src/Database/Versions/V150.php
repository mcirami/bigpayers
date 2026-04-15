<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/27/2018
 * Time: 12:24 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V150 extends Version
{
    public function getVersion()
    {
        return 1.50;
    }

    public function update()
    {
        $db = $this->getDB();

        $sql = "

CREATE TABLE `adjustments_log` (
  `id` int(11) NOT NULL,
  `conversion_id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `adjustments_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversion_id` (`conversion_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `adjustments_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `adjustments_log`
  ADD CONSTRAINT `adjustments_log_ibfk_1` FOREIGN KEY (`conversion_id`) REFERENCES `conversions` (`id`),
  ADD CONSTRAINT `adjustments_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`);


		
		";

        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableExists('adjustments_log');
    }

}