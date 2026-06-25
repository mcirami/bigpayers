<?php

namespace App\Http\Controllers\Report;

use App\Support\CurrentUserContext;
use App\Support\CurrentUserSession;
use App\Support\LegacyPaginate as Paginate;
use App\Support\RequestContext;
use App\User;
use Illuminate\Support\Facades\DB;

class ChatLogReportController extends ReportController
{

    private function report_affiliate($id, ?CurrentUserContext $currentUserContext = null)
    {
        $currentUserContext ??= CurrentUserSession::snapshot();
        $dates = self::getDates();

        if ($id != $currentUserContext->id) {
            if ( ! User::myUsers()->findOrFail($id)->exists) {
                abort(403);
            }
        }

        $rowsPerPage = RequestContext::query('rpp', 10);
        $show = RequestContext::query('show', 'all');
        $query = DB::table('pending_conversions')
            ->join('clicks', 'clicks.idclicks', '=', 'pending_conversions.click_id')
            ->join('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
            ->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
            ->leftJoin('sale_log', 'sale_log.conversion_id', '=', 'conversions.id')
            ->where('clicks.rep_idrep', '=', $id)
            ->whereBetween('pending_conversions.timestamp', [$dates['startDate'], $dates['endDate']]);

        $paginate = new Paginate($rowsPerPage, (clone $query)->count());
        $report = $query
            ->orderByDesc('pending_conversions.timestamp')
            ->limit($rowsPerPage)
            ->offset($paginate->offset())
            ->get([
                'conversions.id as conversion_id',
                'offer.offer_name',
                'pending_conversions.timestamp',
                'pending_conversions.id as pending_conversion_id',
                'conversions.timestamp as conversion_timestamp',
                'sale_log.id as sale_log_id',
            ])
            ->filter(function ($row) use ($show) {
                if ($row->conversion_id === null && $row->sale_log_id === null) {
                    return $show !== 'logged';
                }

                if ($row->sale_log_id !== null) {
                    return $show !== 'nonelogged';
                }

                return false;
            })
            ->values();

        return compact('report', 'paginate', 'dates');
    }

    public function affiliate()
    {
        $currentUserContext = CurrentUserSession::snapshot();

        return view('report.chat-log-affiliate', $this->report_affiliate($currentUserContext->id, $currentUserContext));
    }

    public function admin($userId)
    {
        return view('report.chat-log-affiliate', $this->report_affiliate($userId));
    }

    public function show()
    {
        $currentUser = CurrentUserSession::snapshot()->user();
        $dates = self::getDates();
        $report = DB::table('rep')
            ->leftJoin('clicks', 'clicks.rep_idrep', '=', 'rep.idrep')
            ->leftJoin('pending_conversions', 'pending_conversions.click_id', '=', 'clicks.idclicks')
            ->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
            ->where('rep.lft', '>', $currentUser->lft)
            ->where('rep.rgt', '<', $currentUser->rgt)
            ->whereBetween('pending_conversions.timestamp', [$dates['startDate'], $dates['endDate']])
            ->groupBy('rep.idrep', 'rep.user_name')
            ->orderByDesc('PendingSales')
            ->get([
                'rep.idrep',
                'rep.user_name',
                DB::raw('COUNT(pending_conversions.id) AS PendingSales'),
                DB::raw('SUM(CASE WHEN conversions.id IS NOT NULL THEN 1 ELSE 0 END) AS LoggedSales'),
            ])
            ->map(function ($row) {
                $total = (int) $row->PendingSales;
                $logged = (int) $row->LoggedSales;

                return [
                    'idrep' => $row->idrep,
                    'user_name' => $row->user_name,
                    'pending_sales' => $logged > 0 ? $total - $logged : $total,
                    'logged_sales' => $logged,
                    'total' => $total,
                ];
            });

        return view('report.chat-log', compact('report', 'dates'));
    }

}
