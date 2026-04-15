<?php

namespace LeadMax\TrackYourStats\Report\Repositories;

class BlackListRepository
{

    public function affiliatesBetween($dateFrom, $dateTo, $limit = false, $offset = false)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT idrep, user_name, count(clicks.idclicks) as Clicks  FROM rep INNER JOIN clicks ON clicks.rep_idrep = rep.idrep AND clicks.click_type = 2 AND  clicks.first_timestamp >= :dateFrom AND clicks.first_timestamp <= :dateTo
           GROUP BY idrep ORDER BY Clicks DESC

 ";
        if ($limit && $offset) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        $prep = $db->prepare($sql);
        if ($limit && $offset) {
            $prep->bindParam(":offset", $offset);
            $prep->bindParam(":limit", $limit);
        }

        $dateFrom .= " 00:00:00";
        $dateTo .= " 23:59:59";

        $prep->bindParam(":dateFrom", $dateFrom);
        $prep->bindParam(":dateTo", $dateTo);
        $prep->execute();

        return $prep;
    }

    public function clicksBetween($aff_id, $dateFrom, $dateTo)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM clicks INNER JOIN click_geo ON click_geo.click_id = clicks.idclicks INNER JOIN click_vars ON click_vars.click_id = clicks.idclicks WHERE clicks.click_type = 2 AND clicks.rep_idrep = :aff_id AND clicks.first_timestamp >= :dateFrom and clicks.first_timestamp <= :dateTo";
        $prep = $db->prepare($sql);
        $dateFrom .= " 00:00:00";
        $dateTo .= " 23:59:59";
        $prep->bindParam(":dateFrom", $dateFrom);
        $prep->bindParam(":dateTo", $dateTo);
        $prep->bindParam(":aff_id", $aff_id);
        $prep->execute();

        return $prep;
    }

}