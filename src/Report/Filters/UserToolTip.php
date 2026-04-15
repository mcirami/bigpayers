<?php

namespace LeadMax\TrackYourStats\Report\Filters;

use LeadMax\TrackYourStats\Table\ReportBase;


/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/20/2017
 * Time: 4:49 PM
 */
class UserToolTip implements Filter
{


    public $userNameArrayKey = 'user_name';

    public $userIdArrayKey = 'idrep';

    public function __construct($userNameArrayKey = "user_name", $userIdArrayKey = "idrep")
    {
        $this->userIdArrayKey = $userIdArrayKey;
        $this->userNameArrayKey = $userNameArrayKey;
    }


    public function filter($report)
    {
        $userName = $this->userNameArrayKey;
        $userId = $this->userIdArrayKey;

        foreach ($report as $key => $row) {
            if (isset($row[$userName]) && isset($row[$userId])) {
                $report[$key][$userName] = ReportBase::createUserTooltip($row[$userName], $row[$userId]);
            }
        }

        return $report;
    }

}