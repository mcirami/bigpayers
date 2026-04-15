<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/19/2018
 * Time: 1:03 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V144 extends Version
{

    public function getVersion()
    {
        return 1.44;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "
		
CREATE TABLE `free_sign_ups` (
  `id` int(11) NOT NULL,
  `click_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `free_sign_ups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `click_id` (`click_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `free_sign_ups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `free_sign_ups`
  ADD CONSTRAINT `free_sign_ups_ibfk_1` FOREIGN KEY (`click_id`) REFERENCES `clicks` (`idclicks`),
  ADD CONSTRAINT `free_sign_ups_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`);

		ALTER TABLE `trackyourstats`.`free_sign_ups` ADD INDEX `timestamp` (`timestamp`);
		";

        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableExists('free_sign_ups');
    }

}