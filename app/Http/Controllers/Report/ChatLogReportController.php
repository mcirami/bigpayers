<?php

namespace App\Http\Controllers\Report;

use App\Privilege;
use App\User;
use Carbon\Carbon;
use LeadMax\TrackYourStats\Report\Repositories\AffiliateChatLogRepository;
use LeadMax\TrackYourStats\Report\Repositories\SaleLogRepository;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Paginate;

class ChatLogReportController extends ReportController
{

    private function report_affiliate($id)
    {
        $dates = self::getDates();

        if ($id != Session::userID()) {
            if ( ! User::myUsers()->findOrFail($id)->exists) {
                abort(403);
            }
        }

        $repo = new AffiliateChatLogRepository(\DB::getPdo());
        $repo->setShowOption(request()->query('show', 'all'));
        $repo->setUserId($id);

        if (Session::userType() == Privilege::ROLE_AFFILIATE) {
            $repo->hideConversionId();
        }

        $paginate = new Paginate(request()->query('rpp', 10), $repo->count($dates['startDate'], $dates['endDate']));


        $repo->setLimit(\request()->query('rpp', 10));
        $repo->setOffset($paginate->offset());

        $reporter = new \LeadMax\TrackYourStats\Report\Reporter($repo);

        return compact('reporter', 'paginate', 'dates');
    }

    public function affiliate()
    {
        return view('report.chat-log-affiliate', $this->report_affiliate(Session::userID()));
    }

    public function admin($userId)
    {
        return view('report.chat-log-affiliate', $this->report_affiliate($userId));
    }

    public function show()
    {
        $dates = self::getDates();

        $repo = new SaleLogRepository(\DB::getPdo());


        // Doesn't look like it was being used in legacy page
        //paginate = new \LeadMax\TrackYourStats\Table\Paginate($rpp, $repo->count($d_from, $d_to));


        $reporter = new \LeadMax\TrackYourStats\Report\Reporter($repo);

        $reporter->addFilter(function ($data) {
            $dates = [
                'startDate' => request()->query('d_from', Carbon::today()->format('Y-m-d')),
                'endDate'   => request()->query('d_to', Carbon::today()->format('Y-m-d')),
            ];
            foreach ($data as &$row) {
                $row["TOTAL"] = $row["PendingSales"];
                $row["TOTAL"] = "<a target='_blank' href='/report/chat-log/{$row["idrep"]}?d_from={$dates['startDate']}&d_to={$dates['endDate']}&show=all'>{$row["TOTAL"]}</a>";
                if ($row["LoggedSales"] > 0) {
                    $row["PendingSales"] -= $row["LoggedSales"];
                }
                $row["LoggedSales"] = "<a target='_blank' href='/report/chat-log/{$row["idrep"]}?d_from={$dates['startDate']}&d_to={$dates['endDate']}&show=logged'>{$row["LoggedSales"]}</a>";

                $row["PendingSales"] = "<a target='_blank' href='/report/chat-log/{$row["idrep"]}?d_from={$dates['startDate']}&d_to={$dates['endDate']}&show=nonelogged'>{$row["PendingSales"]}</a>";
            }

            return $data;
        });


        return view('report.chat-log', compact('reporter', 'dates'));
    }

}
