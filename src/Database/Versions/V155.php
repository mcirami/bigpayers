<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * <<<<<<< HEAD
 * Date: 3/23/2018
 * Time: 12:03 PM
 * =======
 * Date: 3/14/2018
 * Time: 3:46 PM
 * >>>>>>> banuser
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V155 extends Version
{

    public function getVersion()
    {
        return 1.55;
    }

    public function update()
    {

        $db = $this->getDB();
        $sql = "ALTER TABLE `permissions` ADD `ban_users` TINYINT(1) NOT NULL DEFAULT '0'; UPDATE permissions SET ban_users = 1 WHERE aff_id = 1;

		
CREATE TABLE `banned_users` (
  `id` INT(11) NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `reason` VARCHAR(255) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires` TIMESTAMP NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `banned_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `banned_users`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `banned_users`
  ADD CONSTRAINT `banned_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`);


 ";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }


    public function verifyUpdate(): bool
    {

        return ($this->tableHasColumns('permissions', ['ban_users']) && $this->tableExists('banned_users'));
    }
}