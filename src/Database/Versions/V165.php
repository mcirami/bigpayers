<?php


namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;


class V165 extends Version
{

    /*
        Version 1.65
        Trello Ticket # 57

        Updates all current affiliates to get the sale_log permission,
        which allows them to use chat_log

     */

    public function getVersion()
    {
        return 1.65;
    }

    public function update()
    {
        $db = $this->getDB();
        $sql = "UPDATE 
                  permissions
                  INNER JOIN privileges ON privileges.is_rep = 1 AND privileges.rep_idrep = permissions.aff_id    
                    
                SET
                  sale_logs = 1
";
        $prep = $db->prepare($sql);

        return $prep->execute();
    }

    public function verifyUpdate(): bool
    {
        $db = $this->getDB();
        $sql = "SELECT * FROM permissions INNER JOIN privileges  ON privileges.is_rep = 1 AND privileges.rep_idrep = permissions.aff_id WHERE permissions.sale_logs = 0";

        $prep = $db->prepare($sql);
        $prep->execute();

        return $prep->rowCount() < 1;
    }

}