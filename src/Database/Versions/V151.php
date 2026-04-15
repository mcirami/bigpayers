<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/1/2018
 * Time: 10:38 AM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V151 extends Version
{
    public function getVersion()
    {
        return 1.51;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "
			ALTER TABLE `rep_has_offer` ADD `deduction_postback` VARCHAR(255) NOT NULL DEFAULT '' AFTER `postback_url`, ADD `free_sign_up_postback` VARCHAR(255) NOT NULL DEFAULT '' AFTER `deduction_postback`;
		";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }


    public function verifyUpdate(): bool
    {
        return $this->tableHasColumns('rep_has_offer', ['deduction_postback', 'free_sign_up_postback']);
    }

}