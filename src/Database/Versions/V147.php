<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/22/2018
 * Time: 1:01 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V147 extends Version
{
    public function getVersion()
    {
        return 1.47;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "CREATE TABLE `referral_deductions` (
  `id` int(11) NOT NULL,
  `referrals_paid_id` int(10) UNSIGNED NOT NULL,
  `deduction_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `referral_deductions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_paid` (`referrals_paid_id`);


ALTER TABLE `referral_deductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableExists('referral_deductions');
    }
}