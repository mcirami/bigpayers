<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Support\CurrentUserSession;
use App\Support\LegacyAffiliatePayoutReport as AffiliatePayout;
use App\Support\LegacyDeductionColumnFilter as DeductionColumnFilter;
use App\Support\LegacyDollarSignFilter as DollarSign;
use App\Support\LegacyEarningPerClickFilter as EarningPerClick;
use App\Support\LegacyAffiliateOfferRepository as AffiliateOfferRepository;
use App\Support\LegacyReporter as Reporter;
use App\Support\LegacyTotalFilter as Total;
use App\Support\RequestContext;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Support\LegacyPayoutLogRepository as PayoutLogRepository;

class PayoutReportController extends ReportController
{


    public function report()
    {
        $report = $this->reportPayout();
        $historyReport = $this->reportPayoutHistory();

        if (RequestContext::expectsJson()) {
            return response($report->toArray());
        }

        return view('report.payout.affiliate', compact('report', 'historyReport'));
    }

    public function invoice()
    {
        $dates = static::getDates();
        $repo = new AffiliateOfferRepository(\DB::getPdo());
        $repo->setAffiliateId(CurrentUserSession::id());
        $offerReporter = new Reporter($repo);
        $offerReporter
            ->addFilter(new DeductionColumnFilter())
            ->addFilter(new Total(['Clicks', 'UniqueClicks', 'FreeSignUps', 'PendingConversions', 'Conversions', 'Revenue', 'Deductions', 'TOTAL'], ['Revenue', 'Deductions']))
            ->addFilter(new EarningPerClick('UniqueClicks', 'Revenue'))
            ->addFilter(new DollarSign(['Revenue', 'Deductions', 'EPC', 'TOTAL']));

        $offerReport = $offerReporter->fetchReport($dates['startDate'], $dates['endDate']);
        $payoutReport = $this->reportPayout();
        $affiliateUserName = CurrentUserSession::user()->user_name;
        $title = strtoupper($affiliateUserName) . '_' . $dates['startDate'] . '_THROUGH_' . $dates['endDate'];

        return \PDF::loadView('pdf.payout-log', compact('affiliateUserName', 'offerReport', 'dates', 'payoutReport', 'title'))->download($title . '.pdf');
    }


    private function reportPayoutHistory()
    {
        $dates = self::getDates();

        $payoutRepository = new PayoutLogRepository(\DB::getPdo());
        $payoutRepository->setUserId(CurrentUserSession::id());

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
        $report = new  AffiliatePayout(CurrentUserSession::id(), $dates['startDate'], $dates['endDate']);

        $report->fetchReports();
        $report->processReports();

        return $report;
    }


}
