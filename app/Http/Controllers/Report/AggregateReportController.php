<?php

namespace App\Http\Controllers\Report;

use App\Privilege;
use App\Support\CurrentUserSession;
use Illuminate\Support\Facades\DB;

class AggregateReportController extends ReportController
{

    /**
     * @return array
     */
    public function show()
    {
        $currentUserContext = CurrentUserSession::snapshot();
        $currentUser = $currentUserContext->user();
        $dates = static::getDates(false);
        $query = DB::table('aggregate_reports')
            ->whereBetween('aggregate_reports.aggregate_date', [$dates['startDate'], $dates['endDate']]);

        if ($currentUserContext->type === Privilege::ROLE_AFFILIATE) {
            $query->where('aggregate_reports.user_id', '=', $currentUserContext->id);
        } else {
            $query->join('rep', 'rep.idrep', '=', 'aggregate_reports.user_id')
                ->where('rep.lft', '>', $currentUser->lft)
                ->where('rep.rgt', '<', $currentUser->rgt);
        }

        $report = $query
            ->groupBy('aggregate_reports.aggregate_date')
            ->orderBy('aggregate_reports.aggregate_date')
            ->get([
                'aggregate_reports.aggregate_date',
                DB::raw('SUM(aggregate_reports.clicks) as clicks'),
                DB::raw('SUM(aggregate_reports.unique_clicks) as unique_clicks'),
                DB::raw('SUM(aggregate_reports.free_sign_ups) as free_sign_ups'),
                DB::raw('SUM(aggregate_reports.pending_conversions) as pending_conversions'),
                DB::raw('SUM(aggregate_reports.conversions) as conversions'),
                DB::raw('SUM(aggregate_reports.revenue) as revenue'),
                DB::raw('SUM(aggregate_reports.deductions) as deductions'),
            ])
            ->map(fn ($row) => [
                'aggregate_date' => $row->aggregate_date,
                'clicks' => $row->clicks,
                'unique_clicks' => $row->unique_clicks,
                'free_sign_ups' => $row->free_sign_ups,
                'pending_conversions' => $row->pending_conversions,
                'conversions' => $row->conversions,
                'revenue' => '$' . number_format((float) $row->revenue, 2),
                'deductions' => '$' . number_format((float) $row->deductions, 2),
            ]);

        return view('report.daily', compact('report'));
    }

}
