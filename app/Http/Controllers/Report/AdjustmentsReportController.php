<?php

namespace App\Http\Controllers\Report;


use App\Http\Controllers\Report\ReportController;
use App\Privilege;
use Carbon\Carbon;
use LeadMax\TrackYourStats\Offer\AdjustmentsLog;
use LeadMax\TrackYourStats\Report\Filters\DollarSign;
use LeadMax\TrackYourStats\Report\Reporter;
use LeadMax\TrackYourStats\Report\Repositories\AdjustmentsLogRepository;
use LeadMax\TrackYourStats\System\Session;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AdjustmentsReportController extends ReportController
{
    public function show()
    {
        $dates = self::getDates();
        $repo = new AdjustmentsLogRepository(\DB::getPdo());
        $repo->setAction(AdjustmentsLog::ACTION_CREATE_SALE);

        if (Session::userType() == Privilege::ROLE_ADMIN) {
            $repo->showOnlyWithThisSaleLogUserId(Session::userID());
        }

        $reporter = new Reporter($repo);
        $reporter->addFilter(new DollarSign(['paid']));

        return view('report.adjustments', compact('reporter','dates'));
    }

}