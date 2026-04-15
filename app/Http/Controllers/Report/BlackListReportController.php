<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BlackListReportController extends ReportController
{

    public function show()
    {
        $dates = self::getDates();
        $report = new \LeadMax\TrackYourStats\Report\BlackList(new \LeadMax\TrackYourStats\Report\Repositories\BlackListRepository());

        $reps = $report->getReport($dates['startDate'], $dates['endDate']);

        return view('report.blacklist', compact('reps'));
    }

}
