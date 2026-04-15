<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * <<<<<<< HEAD
 * Date: 3/8/2018
 * Time: 10:57 AM
 * =======
 * Date: 3/6/2018
 * Time: 11:46 AM
 * >>>>>>> Added sale_log table with V152, basic functionality of sale log dun
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use LeadMax\TrackYourStats\Database\Version;

class V152 extends Version
{

    public function getVersion()
    {
        return 1.52;
    }

    public function update()
    {
        return true;
        $db = $this->getDB();

        $sql = "SELECT idrep, user_id FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_rep = 1 LEFT JOIN report_permissions ON report_permissions.user_id = rep.idrep";

        $prep = $db->prepare($sql);
        $prep->execute();

        $users = $prep->fetchAll(\PDO::FETCH_OBJ);

        $usersThatNeedPermissions = [];

        $questionMarks = [];

        foreach ($users as $user) {
            if ($user->user_id === null) {
                $usersThatNeedPermissions[] = $user->idrep;
                $questionMarks[] = "(?)";
            }
        }


        $sql = "INSERT IGNORE INTO report_permissions(user_id) VALUES";

        $sql .= implode(",", $questionMarks);

        $prep = $db->prepare($sql);


        return $prep->execute($usersThatNeedPermissions);
    }


    public function verifyUpdate(): bool
    {

        return true;
        $db = $this->getDB();

        $sql = "SELECT idrep FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_rep = 1";

        $prep = $db->prepare($sql);

        $prep->execute();

        $affiliateCount = $prep->rowCount();

        $sql = "SELECT user_id FROM report_permissions";

        $prep = $db->prepare($sql);

        $prep->execute();

        $permissionCount = $prep->rowCount();

        return ($affiliateCount == $permissionCount);
    }

}