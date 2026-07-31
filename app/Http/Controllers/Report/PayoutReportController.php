<?php

namespace App\Http\Controllers\Report;

use App\PayoutLog;
use App\Support\CurrentUserContext;
use App\Support\CurrentUserSession;
use App\Support\LegacyAffiliatePayoutReport as AffiliatePayout;
use App\Support\Report\Filters\DeductionColumnFilter;
use App\Support\Report\Filters\DollarSign;
use App\Support\Report\Filters\EarningPerClick;
use App\Support\LegacyAffiliateOfferRepository as AffiliateOfferRepository;
use App\Support\LegacyReporter as Reporter;
use App\Support\Report\Filters\Total;
use App\Support\RequestContext;
use Carbon\Carbon;

class PayoutReportController extends ReportController
{


    public function report()
    {
        $currentUserContext = CurrentUserSession::snapshot();
        $report = $this->reportPayout($currentUserContext);
        $historyReport = $this->reportPayoutHistory($currentUserContext);

        if (RequestContext::expectsJson()) {
            return response($report->toArray());
        }

        return view('report.payout.affiliate', compact('report', 'historyReport'));
    }

    public function invoice()
    {
        $currentUserContext = CurrentUserSession::snapshot();
        $dates = static::getDates();
        $repo = new AffiliateOfferRepository(\DB::getPdo());
        $repo->setAffiliateId($currentUserContext->id);
        $offerReporter = new Reporter($repo);
        $offerReporter
            ->addFilter(new DeductionColumnFilter())
            ->addFilter(new Total(['Clicks', 'UniqueClicks', 'FreeSignUps', 'PendingConversions', 'Conversions', 'Revenue', 'Deductions', 'TOTAL'], ['Revenue', 'Deductions']))
            ->addFilter(new EarningPerClick('UniqueClicks', 'Revenue'))
            ->addFilter(new DollarSign(['Revenue', 'Deductions', 'EPC', 'TOTAL']));

        $offerReport = $offerReporter->fetchReport($dates['startDate'], $dates['endDate']);
        $payoutReport = $this->reportPayout($currentUserContext);
        $affiliateUserName = $currentUserContext->user()->user_name;
        $title = strtoupper($affiliateUserName) . '_' . $dates['startDate'] . '_THROUGH_' . $dates['endDate'];

        return \PDF::loadView('pdf.payout-log', compact('affiliateUserName', 'offerReport', 'dates', 'payoutReport', 'title'))->download($title . '.pdf');
    }


    private function reportPayoutHistory(?CurrentUserContext $currentUserContext = null)
    {
        $currentUserContext ??= CurrentUserSession::snapshot();

        return PayoutLog::query()
            ->where('user_id', '=', $currentUserContext->id)
            ->get()
            ->map(function (PayoutLog $payoutLog) {
                $row = $payoutLog->toArray();
                $row['deductions'] = $row['deductions'] > 0
                    ? -1 * $row['deductions']
                    : $row['deductions'];
                $row['TOTAL'] = array_sum([
                    $row['revenue'],
                    $row['deductions'],
                    $row['bonuses'],
                    $row['referrals'],
                ]);

                foreach (['revenue', 'deductions', 'bonuses', 'referrals', 'TOTAL'] as $key) {
                    $row[$key] = '$' . number_format((float) $row[$key], 2);
                }

                foreach (['start_of_week', 'end_of_week'] as $key) {
                    if (isset($row[$key])) {
                        $row[$key] = Carbon::createFromTimeString($row[$key])->format('Y-m-d');
                    }
                }

                return $row;
            })
            ->all();
    }

    private function reportPayout(?CurrentUserContext $currentUserContext = null)
    {
        $currentUserContext ??= CurrentUserSession::snapshot();
        $dates = static::getDates();
        $report = new AffiliatePayout($currentUserContext->id, $dates['startDate'], $dates['endDate']);

        $report->fetchReports();
        $report->processReports();

        return $report;
    }


}
