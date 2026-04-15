<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/20/2017
 * Time: 4:06 PM
 */

namespace LeadMax\TrackYourStats\Report\Repositories;


use LeadMax\TrackYourStats\Clicks\Click;
use LeadMax\TrackYourStats\System\Session;

class SubVarRepository extends Repository
{

    const SUB_1 = 1;
    const SUB_2 = 2;
    const SUB_3 = 3;
//    const SUB_4 = 4;
//    const SUB_5 = 5;

    private $SUB_NUM = self::SUB_1;

    private $conversions = array();

    public function __construct($db_connection)
    {
        parent::__construct($db_connection);
    }

    public function setSubNumber(int $SUB_NUM)
    {
        if ($SUB_NUM >= 1 && $SUB_NUM <= 3 && is_numeric($SUB_NUM)) {
            $this->SUB_NUM = $SUB_NUM;
        }
    }


    private function addClickCounts(&$sorted, $click)
    {

        $sub = $this->getSubVarString($this->SUB_NUM);


        // if click is raw or unique still adds to total count...
        $sorted[$click[$sub]]["clicks"]++;


        if ($click["click_type"] == Click::TYPE_UNIQUE) {
            $sorted[$click[$sub]]["unique"]++;
        }


//        //joining conversions table to grab user_id instead of conversions.id b/c number varies and can be quite large,
//        //so just pull user_id b/c constant number that isnt big
//        if ($click["paid"] !== null) {
//            $sorted[$click[$sub]]["conversions"]++;
//            $sorted[$click[$sub]]["revenue"] += $click["paid"];
//        }
    }


    private function getSubVarString($SUB_VAR)
    {
        switch ($SUB_VAR) {
            case 1:
                return "sub1";

            case 2:
                return "sub2";

            case 3:
                return "sub3";

//            case 4: return "sub4";
//
//            case 5: return "sub5";

            default :
                return "sub1";
        }
    }

    private function findRevenue($dateFrom, $dateTo)
    {
        $db = $this->getDB();
        $sql = "SELECT ";
        $sql .= $this->getSubVarString($this->SUB_NUM);
        $sql .= ", sum(conversions.paid) as Revenue, count(conversions.paid) as Conversions from conversions
        
        INNER JOIN clicks ON clicks.rep_idrep = :user_id AND clicks.idclicks = conversions.click_id AND clicks.click_type != 2
        INNER JOIN click_vars ON click_vars.click_id = clicks.idclicks 
         
        WHERE conversions.timestamp BETWEEN :dateFrom AND :dateTo
        
        GROUP BY ";
        $sql .= $this->getSubVarString($this->SUB_NUM);

        $prep = $db->prepare($sql);

        $prep->bindParam(":dateFrom", $dateFrom);
        $prep->bindParam(":dateTo", $dateTo);

        $userId = Session::userID();
        $prep->bindParam(":user_id", $userId);


        if ($prep->execute()) {
            $result = $prep->fetchAll(\PDO::FETCH_ASSOC);
            $revenue = [];

            foreach ($result as $sum) {
                $revenue[$sum[$this->getSubVarString($this->SUB_NUM)]] = [
                    "revenue" => $sum["Revenue"],
                    "conversions" => $sum["Conversions"],
                ];
            }

            return $revenue;
        } else {
            return false;
        }
    }

    protected function query($dateFrom, $dateTo): \PDOStatement
    {
        $db = $this->getDB();


        $sql = "SELECT ";

        $sql .= $this->getSubVarString($this->SUB_NUM);

        $sql .= ", click_type FROM clicks

INNER JOIN click_vars ON click_vars.click_id = clicks.idclicks

WHERE clicks.click_type != 2 AND clicks.rep_idrep = :user_id AND clicks.first_timestamp BETWEEN :dateFrom AND :dateTo 

";


        $prep = $db->prepare($sql);
        $user_id = Session::userID();

        $prep->bindParam(":user_id", $user_id);
        $prep->bindParam(":dateFrom", $dateFrom);
        $prep->bindParam(":dateTo", $dateTo);


        $prep->execute();

        return $prep;
    }

    public function between($dateFrom, $dateTo): array
    {
        $stmt = $this->query($dateFrom, $dateTo);


        return $this->formatResult($stmt->fetchAll(\PDO::FETCH_ASSOC), $this->findRevenue($dateFrom, $dateTo));
    }


    public function count($dateFrom, $dateTo): int
    {
        $report = $this->query($dateFrom, $dateTo)->fetchAll(\PDO::FETCH_ASSOC);

        return count($this->formatResult($report, $this->findRevenue($dateFrom, $dateTo)));
    }

    private function formatResult($report, $revenue)
    {

        $sorted = [];

        $sub = $this->getSubVarString($this->SUB_NUM);

        foreach ($report as $click) {

            if (isset($sorted[$click[$sub]]) == false) {

                $sorted[$click[$sub]] = ['clicks' => 0, 'unique' => 0, 'conversions' => 0, 'revenue' => 0];

            }

            $this->addClickCounts($sorted, $click);
        }


        $newReport = [];

        foreach ($sorted as $key => $row) {
            if ($key == "") {
                $newReport["(empty)"] = $row;
            } else {
                $newReport[$key] = $row;
            }
        }


        // the keyName is the click's  sub1-5, but since the way output interfaces work, add the keyName (subName of clicks) to the array with name 'sub'
        foreach ($newReport as $keyName => &$item) {
            $item = array_merge(['sub' => $keyName], $item);
        }


        if (isset($revenue[""])) {
            $revenue["(empty)"] = $revenue[""];
            unset($revenue[""]);
        }


        foreach ($revenue as $sub => $val) {
            if (isset($newReport[$sub])) {
                $newReport[$sub]["revenue"] = $val["revenue"];
                $newReport[$sub]["conversions"] = $val["conversions"];
            } else {
                $newReport[$sub] = [
                    'clicks' => 0,
                    'unique' => 0,
                    'conversions' => $val["conversions"],
                    'revenue' => $val["revenue"],
                ];
            }
        }


        return $newReport;
    }


}