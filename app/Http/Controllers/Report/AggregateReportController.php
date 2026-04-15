<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\AggregateServiceProvider;
use LeadMax\TrackYourStats\Report\Filters\DollarSign;
use LeadMax\TrackYourStats\Report\Formats\HTML;
use LeadMax\TrackYourStats\Report\Reporter;
use LeadMax\TrackYourStats\Report\Repositories\AggregateReportRepository;
use LeadMax\TrackYourStats\System\Session;

class AggregateReportController extends ReportController
{

    /**
     * @return array
     */
    public function show()
    {
        $dates = static::getDates(false);
        $repo = new AggregateReportRepository(\DB::getPdo());
        $repo->setUser(Session::user());
        $reporter = new Reporter($repo);
        $reporter->addFilter(new DollarSign(['revenue', 'deductions']));
        $report = $reporter->fetchReport($dates['startDate'], $dates['endDate']);

        return view('report.daily', compact('report'));
    }

}
