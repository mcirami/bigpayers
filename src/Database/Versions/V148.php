<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/23/2018
 * Time: 12:30 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;
use LeadMax\TrackYourStats\User\ReportPermissions;

class V148 extends Version
{

    public function getVersion()
    {
        return 1.48;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "
CREATE TABLE `report_permissions` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `offer_id` tinyint(1) NOT NULL DEFAULT '1',
  `offer_name` tinyint(1) NOT NULL DEFAULT '1',
  `raw_clicks` tinyint(1) NOT NULL DEFAULT '1',
  `unique_clicks` tinyint(1) NOT NULL DEFAULT '1',
  `conversions` tinyint(1) NOT NULL DEFAULT '1',
  `revenue` tinyint(1) NOT NULL DEFAULT '1',
  `epc` tinyint(1) NOT NULL DEFAULT '1',
  `free_sign_ups` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `report_permissions`
  ADD PRIMARY KEY (`user_id`);

ALTER TABLE `report_permissions`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `report_permissions`
  ADD CONSTRAINT `report_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rep` (`idrep`);

ALTER TABLE `permissions` ADD `edit_report_permissions` TINYINT NOT NULL DEFAULT '0' AFTER `edit_affiliates`;
UPDATE permissions SET edit_report_permissions = 1 WHERE aff_id = 1;
";

        $prep = $db->prepare($sql);


        if ($prep->execute()) {
            ReportPermissions::createPermissionsForAllAffiliates($db);

            return true;
        } else {
            return false;
        }
    }

    public function verifyUpdate(): bool
    {
        return $this->tableExists('report_permissions');
    }
}