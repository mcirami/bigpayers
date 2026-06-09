<?php

namespace App\Http\Controllers\Report;

use App\Support\LegacyBlackListReport as BlackListReport;
use App\Support\LegacyBlackListRepository as BlackListRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BlackListReportController extends ReportController
{

    public function show()
    {
        $dates = self::getDates();
        $report = new BlackListReport(new BlackListRepository());

        $reps = $report->getReport($dates['startDate'], $dates['endDate']);

        return view('report.blacklist', compact('reps'));
    }

}
