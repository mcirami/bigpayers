<?php

namespace App\Http\Controllers\Report;

use App\Privilege;
use App\Support\CurrentUserContext;
use App\Support\CurrentUserSession;
use App\Support\LegacyAffiliateChatLogRepository as AffiliateChatLogRepository;
use App\Support\LegacyPaginate as Paginate;
use App\Support\LegacyReporter as Reporter;
use App\Support\LegacySaleLogRepository as SaleLogRepository;
use App\Support\RequestContext;
use App\User;

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

        $repo = new AffiliateChatLogRepository(\DB::getPdo());
        $repo->setShowOption(RequestContext::query('show', 'all'));
        $repo->setUserId($id);

        if ($currentUserContext->type == Privilege::ROLE_AFFILIATE) {
            $repo->hideConversionId();
        }

        $rowsPerPage = RequestContext::query('rpp', 10);
        $paginate = new Paginate($rowsPerPage, $repo->count($dates['startDate'], $dates['endDate']));


        $repo->setLimit($rowsPerPage);
        $repo->setOffset($paginate->offset());

        $reporter = new Reporter($repo);

        return compact('reporter', 'paginate', 'dates');
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
        $dates = self::getDates();

        $repo = new SaleLogRepository(\DB::getPdo());


        // Doesn't look like it was being used in legacy page
        // Pagination was not used in the legacy page.


        $reporter = new Reporter($repo);

        $reporter->addFilter(function ($data) use ($dates) {
            foreach ($data as &$row) {
                $row["TOTAL"] = $row["PendingSales"];
                $row["TOTAL"] = "<a target='_blank' href='/report/chat-log/{$row["idrep"]}?d_from={$dates['originalStart']}&d_to={$dates['originalEnd']}&show=all'>{$row["TOTAL"]}</a>";
                if ($row["LoggedSales"] > 0) {
                    $row["PendingSales"] -= $row["LoggedSales"];
                }
                $row["LoggedSales"] = "<a target='_blank' href='/report/chat-log/{$row["idrep"]}?d_from={$dates['originalStart']}&d_to={$dates['originalEnd']}&show=logged'>{$row["LoggedSales"]}</a>";

                $row["PendingSales"] = "<a target='_blank' href='/report/chat-log/{$row["idrep"]}?d_from={$dates['originalStart']}&d_to={$dates['originalEnd']}&show=nonelogged'>{$row["PendingSales"]}</a>";
            }

            return $data;
        });


        return view('report.chat-log', compact('reporter', 'dates'));
    }

}
