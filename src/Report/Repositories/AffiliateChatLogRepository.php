<?php

namespace LeadMax\TrackYourStats\Report\Repositories;


class AffiliateChatLogRepository extends Repository
{

    public $userId;

    public $OPTION_SHOW = "all";

    const OPTION_SHOW_ALL = "all";
    const OPTION_SHOW_LOGGED = "logged";
    const OPTION_SHOW_NONE_LOGGED = "nonelogged";

    public $hideConversionId = false;

    public function __construct($db_connection)
    {
        parent::__construct($db_connection);
    }


    public function setShowOption($get)
    {
        $this->OPTION_SHOW = $get;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function format($report)
    {
        foreach ($report as $key => $row) {
            if ($row["ConversionId"] === null && $row["saleLogId"] === null) {
                $report[$key]["saleLogId"] = "<a target='' href='/chat-log/add/{$row["pendingConversionId"]}' class='btn btn-sm btn-default' >Log Sale</a>";
                unset($report[$key]["pendingConversionId"]);

                if ($this->OPTION_SHOW == self::OPTION_SHOW_LOGGED) {
                    unset($report[$key]);
                }

            } else {
                if ( ! is_null($row["saleLogId"])) {
                    $report[$key]["saleLogId"] = "<a target='' href='/sale_log_view.php?id={$row["saleLogId"]}' class='btn btn-sm btn-default' >View Log</a>";
                    unset($report[$key]["pendingConversionId"]);

                    if ($this->OPTION_SHOW == self::OPTION_SHOW_NONE_LOGGED) {
                        unset($report[$key]);
                    }

                } else {
                    // remove log that has a conversion but no saleLog
                    unset($report[$key]);
                }
            }

            if ($this->hideConversionId) {
                unset($report[$key]["ConversionId"]);
            }

        }

        return $report;
    }

    public function between($dateFrom, $dateTo): array
    {
        return $this->format($this->query($dateFrom, $dateTo)->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function query($dateFrom, $dateTo): \PDOStatement
    {
        $db = $this->getDB();

        $sql = "SELECT 
                    conversions.id AS ConversionId, 
                    offer.offer_name,
                    pending_conversions.timestamp,
                    pending_conversions.id as pendingConversionId,
                    conversions.timestamp as conversionTimestamp,
                    sale_log.id AS saleLogId 
                FROM pending_conversions
                    INNER JOIN clicks 
                        ON clicks.idclicks = pending_conversions.click_id
                    INNER JOIN offer 
                        ON offer.idoffer = clicks.offer_idoffer
                    LEFT JOIN conversions 
                        ON conversions.click_id = clicks.idclicks
                    LEFT JOIN sale_log 
                        ON sale_log.conversion_id = conversions.id
                WHERE clicks.rep_idrep = :user_id
                    AND pending_conversions.timestamp BETWEEN :dateFrom AND :dateTo
                ORDER BY pending_conversions.timestamp DESC
                
                ";

        if (isset($this->limit)) {
            $sql .= " LIMIT :limit";
        }

        if (isset($this->offset)) {
            $sql .= " OFFSET :offset ";
        }


        $prep = $db->prepare($sql);


        $prep->bindParam(":dateFrom", $dateFrom);
        $prep->bindParam(":dateTo", $dateTo);
        $prep->bindValue(":user_id", $this->userId);

        if (isset($this->limit)) {
            $prep->bindParam(":limit", $this->limit);
        }

        if (isset($this->offset)) {
            $prep->bindParam(":offset", $this->offset);
        }


        $prep->execute();


        return $prep;
    }

    public function hideConversionId()
    {
        $this->hideConversionId = true;
    }


}