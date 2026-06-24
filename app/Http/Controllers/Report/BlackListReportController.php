<?php

namespace App\Http\Controllers\Report;

use App\Click;
use App\Support\RequestContext;
use Illuminate\Support\Facades\DB;

class BlackListReportController extends ReportController
{

    public function show()
    {
        $dates = self::getDates();
        $reps = DB::table('rep')
            ->join('clicks', 'clicks.rep_idrep', '=', 'rep.idrep')
            ->where('clicks.click_type', '=', Click::TYPE_BLACKLISTED)
            ->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
            ->groupBy('rep.idrep', 'rep.user_name')
            ->orderByDesc('blacklisted_clicks')
            ->get([
                'rep.idrep',
                'rep.user_name',
                DB::raw('COUNT(clicks.idclicks) as blacklisted_clicks'),
            ]);

        return view('report.blacklist', [
            'dateSelect' => RequestContext::query('dateSelect'),
            'endDate' => $dates['originalEnd'],
            'reps' => $reps,
            'startDate' => $dates['originalStart'],
        ]);
    }

}
