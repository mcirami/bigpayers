<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use LeadMax\TrackYourStats\Report\AffiliatePayout;
use LeadMax\TrackYourStats\Report\Filters\DeductionColumnFilter;
use LeadMax\TrackYourStats\Report\Filters\DollarSign;
use LeadMax\TrackYourStats\Report\Filters\Total;
use LeadMax\TrackYourStats\Report\Reporter;
use LeadMax\TrackYourStats\Report\Repositories\Offer\AffiliateOfferRepository;
use LeadMax\TrackYourStats\Report\Repositories\PayoutLogRepository;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Report\Filters;

class PayoutReportController extends ReportController
{


    public function report()
    {
        $report = $this->reportPayout();
        $historyReport = $this->reportPayoutHistory();

        if (request()->expectsJson()) {
            return response($report->toArray());
        }

        return view('report.payout.affiliate', compact('report', 'historyReport'));
    }

    public function invoice()
    {
        $dates = static::getDates();
        $repo = new AffiliateOfferRepository(\DB::getPdo());
        $repo->setAffiliateId(Session::userID());
        $offerReporter = new Reporter($repo);
        $offerReporter
            ->addFilter(new Filters\DeductionColumnFilter())
            ->addFilter(new Filters\Total(['Clicks', 'UniqueClicks', 'FreeSignUps', 'PendingConversions', 'Conversions', 'Revenue', 'Deductions', 'TOTAL'], ['Revenue', 'Deductions']))
            ->addFilter(new Filters\EarningPerClick('UniqueClicks', 'Revenue'))
            ->addFilter(new Filters\DollarSign(['Revenue', 'Deductions', 'EPC', 'TOTAL']));

        $offerReport = $offerReporter->fetchReport($dates['startDate'], $dates['endDate']);
        $payoutReport = $this->reportPayout();
        $title = strtoupper(Session::user()->user_name) . '_' . $dates['startDate'] . '_THROUGH_' . $dates['endDate'];

        return \PDF::loadView('pdf.payout-log', compact('offerReport', 'dates', 'payoutReport', 'title'))->download($title . '.pdf');
    }


    private function reportPayoutHistory()
    {
        $dates = self::getDates();

        $payoutRepository = new PayoutLogRepository(\DB::getPdo());
        $payoutRepository->setUserId(Session::userID());

        $reporter = new Reporter($payoutRepository);


        $reporter
            ->addFilter(new DeductionColumnFilter('deductions'))
            ->addFilter(new Total([], ['revenue', 'deductions', 'bonuses', 'referrals']))
            ->addFilter(new DollarSign(['revenue', 'deductions', 'bonuses', 'referrals', 'TOTAL']))
            ->addFilter(function ($data) {
                // Remove the total row
                array_pop($data);
                foreach ($data as &$row) {
                    foreach (['start_of_week', 'end_of_week'] as $key) {
                        if (isset($row[$key])) {
                            $row[$key] = Carbon::createFromTimeString($row[$key])->format('Y-m-d');
                        }
                    }
                }


                return $data;
            });


        return $reporter->fetchReport($dates['startDate'], $dates['endDate']);
    }

    private function reportPayout()
    {
        $dates = static::getDates();
        $report = new  AffiliatePayout(Session::userID(), $dates['startDate'], $dates['endDate']);

        $report->fetchReports();
        $report->processReports();

        return $report;
    }


}
