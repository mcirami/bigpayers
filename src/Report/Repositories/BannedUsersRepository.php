<?php

namespace LeadMax\TrackYourStats\Report\Repositories;


use Carbon\Carbon;
use LeadMax\TrackYourStats\System\Session;

class BannedUsersRepository extends Repository
{
    public function query($dateFrom, $dateTo): \PDOStatement
    {
        $db = $this->getDB();
        $sql = "SELECT user_id, user_name, banned_users.timestamp, expires, reason, banned_users.status FROM banned_users

				  INNER JOIN rep ON rep.idrep = banned_users.user_id
				  				AND rep.lft > :left AND rep.rgt < :right
				  
				  ";
        $prep = $db->prepare($sql);
        $prep->bindParam(":left", Session::userData()->lft);
        $prep->bindParam(":right", Session::userData()->rgt);

        $prep->execute();

        return $prep;
    }

    private function format($report)
    {
        foreach ($report as &$row) {
            $row["status"] = ($row["status"] === 1 && $row["expires"] > Carbon::now()->format('Y-m-d H:i:s')) ? "Active" : "In-Active";
            $row["actions"] = "<a target='_blank' class='btn btn-default btn-sm' href='ban_user_edit.php?uid={$row["user_id"]}'>Ban Settings</a>";
        }

        return $report;
    }

    public function between($dateFrom, $dateTo): array
    {
        return $this->format($this->query($dateFrom, $dateTo)->fetchAll(\PDO::FETCH_ASSOC));
    }
}