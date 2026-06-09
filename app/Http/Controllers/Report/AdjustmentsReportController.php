<?php

namespace App\Http\Controllers\Report;


use App\Http\Controllers\Report\ReportController;
use App\Privilege;
use App\Support\CurrentUserSession;
use App\Support\LegacyAdjustmentsLog as AdjustmentsLog;
use App\Support\LegacyAdjustmentsLogRepository as AdjustmentsLogRepository;
use App\Support\LegacyDollarSignFilter as DollarSign;
use App\Support\LegacyReporter as Reporter;
use Carbon\Carbon;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AdjustmentsReportController extends ReportController
{
    public function show()
    {
        $dates = self::getDates();
        $repo = new AdjustmentsLogRepository(\DB::getPdo());
        $repo->setAction(AdjustmentsLog::ACTION_CREATE_SALE);

        if (CurrentUserSession::type() == Privilege::ROLE_ADMIN) {
            $repo->showOnlyWithThisSaleLogUserId(CurrentUserSession::id());
        }

        $reporter = new Reporter($repo);
        $reporter->addFilter(new DollarSign(['paid']));

        return view('report.adjustments', compact('reporter','dates'));
    }

}
