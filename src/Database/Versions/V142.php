<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/6/2018
 * Time: 3:23 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V142 extends Version
{

    public function getVersion()
    {
        return 1.42;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "  CREATE TABLE `user_postbacks` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `user_postbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `user_postbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_postbacks`
  ADD CONSTRAINT `user_postbacks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`);
  ";

        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableExists('user_postbacks');
    }

}