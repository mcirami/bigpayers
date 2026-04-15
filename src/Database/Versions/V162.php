<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 4/10/2018
 * Time: 1:36 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V162 extends Version
{

    public function getVersion()
    {
        return 1.62;
    }

    public function update()
    {
        $sql = "
				SET FOREIGN_KEY_CHECKS =0;
				
				DELETE click_geo, clicks, click_vars FROM clicks
INNER JOIN click_vars ON click_vars.click_id = clicks.idclicks
INNER JOIN click_geo ON click_geo.click_id = clicks.idclicks
WHERE clicks.rep_idrep = 0;
		 		
		 		SET FOREIGN_KEY_CHECKS =1;
		 		";

        return $this->getDB()->prepare($sql)->execute();
    }

    public function verifyUpdate(): bool
    {
        $sql = "SELECT * FROM clicks WHERE rep_idrep = 0";
        $prep = $this->getDB()->prepare($sql);
        $prep->execute();

        return ($prep->rowCount() == 0);
    }

}