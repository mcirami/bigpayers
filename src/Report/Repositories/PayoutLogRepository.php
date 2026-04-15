<?php

namespace LeadMax\TrackYourStats\Report\Repositories;


use App\PayoutLog;

class PayoutLogRepository extends Repository
{

    private $userId;


    public function setUserId($id)
    {
        $this->userId = $id;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    protected function query($dateFrom, $dateTo): \PDOStatement
    {
        // TODO: Implement query() method.
    }

    public function between($dateFrom, $dateTo): array
    {
        return PayoutLog::where([
            ['user_id', '=', $this->userId],
//            ['start_of_week', '>=', $dateFrom],
//            ['end_of_week', '=<', $dateTo]
        ])->get()->toArray();
    }
}