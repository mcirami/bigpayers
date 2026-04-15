<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/27/2018
 * Time: 1:08 PM
 */

namespace LeadMax\TrackYourStats\Report\Repositories;


use LeadMax\TrackYourStats\Offer\AdjustmentsLog;

class AdjustmentsLogRepository extends Repository
{

    public $OPT_SHOW_ALL = true;

    public $user_id;

    public $action;

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function showOnlyWithThisSaleLogUserId($user_id)
    {
        $this->OPT_SHOW_ALL = false;
        $this->user_id = $user_id;
    }

    public function showAll()
    {
        $this->OPT_SHOW_ALL = true;
        unset($this->user_id);
    }

    private function replaceActionColumnWithWord($report)
    {
        foreach ($report as $key => &$row) {
            switch ($row["action"]) {
                case AdjustmentsLog::ACTION_CREATE_SALE:
                    $row["action"] = "CREATE SALE";
                    break;

                case AdjustmentsLog::ACTION_EDIT_SALE:
                    $row["action"] = "EDIT SALE";
                    break;

                case AdjustmentsLog::ACTION_DEDUCT_SALE:
                    $row["action"] = "DEDUCT SALE";
                    break;
            }
        }

        return $report;
    }

    public function query($dateFrom, $dateTo): \PDOStatement
    {
        $db = $this->getDB();
        $sql = "SELECT adjustments_log.id, Affiliate.user_name as AffiliateUserName, conversions.click_id, offer.offer_name, conversions.id as conversion_id, conversions.paid, conversions.timestamp, CreatorUser.user_name as CreatorUserName, adjustments_log.action
 				FROM	adjustments_log
 				INNER JOIN conversions ON conversions.id = adjustments_log.conversion_id
 				INNER JOIN clicks ON clicks.idclicks = conversions.click_id
 				INNER JOIN offer ON offer.idoffer = clicks.offer_idoffer
 				LEFT JOIN rep as CreatorUser ON CreatorUser.idrep = adjustments_log.user_id
 				LEFT JOIN rep as Affiliate ON Affiliate.idrep = conversions.user_id
 				
 				WHERE adjustments_log.timestamp BETWEEN :dateFrom AND :dateTo AND adjustments_log.action = :action ";

        if ($this->OPT_SHOW_ALL == false) {
            $sql .= " AND adjustments_log.user_id = :user_id";
        }


        $sql .= " ORDER BY adjustments_log.id DESC";

        $prep = $db->prepare($sql);

        if ($this->OPT_SHOW_ALL == false) {
            $prep->bindParam(":user_id", $this->user_id);
        }

        $prep->bindParam(":dateFrom", $dateFrom);
        $prep->bindParam(":dateTo", $dateTo);
        $prep->bindParam(":action", $this->action);
        $prep->execute();

        return $prep;
    }

    public function between($dateFrom, $dateTo): array
    {
        return $this->replaceActionColumnWithWord($this->query($dateFrom, $dateTo)->fetchAll(\PDO::FETCH_ASSOC));
    }

}