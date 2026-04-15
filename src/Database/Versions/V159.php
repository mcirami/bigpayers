<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 4/3/2018
 * Time: 10:10 AM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V159 extends Version
{

    public function getVersion()
    {
        return 1.59;
    }

    public function update()
    {
        $sql = " ALTER TABLE banned_users ADD UNIQUE user_id_unique (user_id);
		";

        $prep = $this->getDB()->prepare($sql);

        return $prep->execute();
    }


    public function verifyUpdate(): bool
    {
        return $this->tableHasIndexes('banned_users', ['user_id_unique']);
    }
}