<?php

namespace LeadMax\TrackYourStats\Report;

use LeadMax\TrackYourStats\Report\Repositories\BlackListRepository;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/30/2017
 * Time: 4:02 PM
 */
class BlackList
{

    public $repo;

    public function __construct(BlackListRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getReport($dateFrom, $dateTo)
    {
        return $this->repo->affiliatesBetween($dateFrom, $dateTo)->fetchAll(\PDO::FETCH_ASSOC);
    }


}