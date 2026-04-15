<?php

namespace LeadMax\TrackYourStats\Report\Repositories;


use LeadMax\TrackYourStats\System\Session;

class SaleLogRepository extends Repository
{

    public function query($dateFrom, $dateTo): \PDOStatement
    {
        $db = $this->getDB();
        $sql = "SELECT
                    rep.idrep,
                    rep.user_name,
                    count(pending_conversions.id) AS PendingSales,
                    SUM(CASE WHEN conversions.id IS NOT NULL THEN 1 ELSE 0 END) as LoggedSales
                FROM rep
                    LEFT JOIN clicks 
                        ON clicks.rep_idrep = rep.idrep
                    LEFT JOIN pending_conversions 
                        ON pending_conversions.click_id = clicks.idclicks
                    LEFT JOIN conversions 
                        ON conversions.click_id = clicks.idclicks
                WHERE  rep.lft > :left AND rep.rgt < :right 
                    AND pending_conversions.timestamp BETWEEN :dateFrom AND :dateTo 
                GROUP BY rep.idrep, rep.user_name
                ORDER BY PendingSales DESC    
                ";

        $prep = $db->prepare($sql);
        $left = Session::userData()->lft;
        $right = Session::userData()->rgt;
        $prep->bindParam(":left", $left);
        $prep->bindParam(":right", $right);


        $prep->bindParam(":dateFrom", $dateFrom);
        $prep->bindParam(":dateTo", $dateTo);


        $prep->execute();


        return $prep;
    }


    public function between($dateFrom, $dateTo): array
    {

        $report = $this->query($dateFrom, $dateTo)->fetchAll(\PDO::FETCH_ASSOC);

        return $report;
    }

}