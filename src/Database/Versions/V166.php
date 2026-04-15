<?php


namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V166 extends Version
{

    public function getVersion()
    {
        return 1.66;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "
CREATE TABLE `pending_conversions` (
  `id` int(10) UNSIGNED NOT NULL,
  `click_id` int(10) UNSIGNED NOT NULL,
  `payout` double NOT NULL,
  `converted` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `pending_conversions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pending_conversions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  ALTER TABLE pending_conversions ADD FOREIGN KEY (click_id) REFERENCES clicks(idclicks)

";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        return $this->tableExists('pending_conversions');
    }

}