<?php

namespace App\Http\Controllers\Report;


use App\Privilege;
use App\Support\CurrentUserSession;
use App\Support\LegacyAdjustmentsLog as AdjustmentsLog;
use Illuminate\Support\Facades\DB;

class AdjustmentsReportController extends ReportController
{
    public function show()
    {
        $currentUserContext = CurrentUserSession::snapshot();
        $dates = self::getDates();
        $query = DB::table('adjustments_log')
            ->join('conversions', 'conversions.id', '=', 'adjustments_log.conversion_id')
            ->join('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
            ->join('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
            ->leftJoin('rep as creator_user', 'creator_user.idrep', '=', 'adjustments_log.user_id')
            ->leftJoin('rep as affiliate', 'affiliate.idrep', '=', 'conversions.user_id')
            ->whereBetween('adjustments_log.timestamp', [$dates['startDate'], $dates['endDate']])
            ->where('adjustments_log.action', '=', AdjustmentsLog::ACTION_CREATE_SALE);

        if ($currentUserContext->type == Privilege::ROLE_ADMIN) {
            $query->where('adjustments_log.user_id', '=', $currentUserContext->id);
        }

        $report = $query
            ->orderByDesc('adjustments_log.id')
            ->get([
                'adjustments_log.id',
                'affiliate.user_name as affiliate_user_name',
                'conversions.click_id',
                'offer.offer_name',
                'conversions.id as conversion_id',
                'conversions.paid',
                'conversions.timestamp',
                'creator_user.user_name as creator_user_name',
            ]);

        return view('report.adjustments', compact('report', 'dates'));
    }

}
