<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/22/2018
 * Time: 12:56 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V145 extends Version
{

    public function getVersion()
    {
        return 1.45;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "ALTER TABLE `user_postbacks` ADD `free_sign_up_url` VARCHAR(255) NOT NULL AFTER `url`,
				ADD `deduction_url` VARCHAR(255) NOT NULL AFTER `free_sign_up_url`;
				ALTER TABLE `user_postbacks` CHANGE `url` `url` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '', CHANGE `free_sign_up_url` `free_sign_up_url` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '', CHANGE `deduction_url` `deduction_url` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '';
				
				";

        $prep = $db->prepare($sql);

        return $prep->execute();
    }


    public function verifyUpdate(): bool
    {
        return $this->tableHasColumns('user_postbacks', ['free_sign_up_url', 'deduction_url']);
    }
}