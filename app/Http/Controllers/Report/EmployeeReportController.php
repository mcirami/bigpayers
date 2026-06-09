<?php

namespace App\Http\Controllers\Report;

use App\Privilege;
use App\Support\CurrentUserSession;
use App\Support\LegacyDeductionColumnFilter as DeductionColumnFilter;
use App\Support\LegacyDollarSignFilter as DollarSign;
use App\Support\LegacyEarningPerClickFilter as EarningPerClick;
use App\Support\LegacyReporter as Reporter;
use App\Support\LegacyTotalFilter as Total;
use App\Support\LegacyUserToolTipFilter as UserToolTip;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\Report\Repositories\Employee\GodEmployeeRepository;
use LeadMax\TrackYourStats\Report\Repositories\Employee\AdminEmployeeRepository;
use LeadMax\TrackYourStats\Report\Repositories\Employee\ManagerEmployeeRepository;
use LeadMax\TrackYourStats\Report\Repositories\Repository;

class EmployeeReportController extends ReportController
{


    private function report(Repository $repository, Request $request)
    {
        $repository->SHOW_AFF_TYPE = $request->query('role', 3);
        $isGodUser = CurrentUserSession::type() === Privilege::ROLE_GOD;
	    $SmsStatsPermission = CurrentUserSession::can('view_sms_stats');

        $reporter = new Reporter($repository);

        $totals = [
            'Clicks',
            'UniqueClicks',
            'FreeSignUps',
            'PendingConversions',
            'Conversions',
            'Revenue',
            'BonusRevenue',
            'ReferralRevenue',
            'TOTAL'
        ];

        if ($isGodUser || $SmsStatsPermission) {
            $totals[] = 'Codes';
        } else {
            $totals[] = 'Deductions';
        }

        $currencyColumns = [
            'EPC',
            'Revenue',
            'BonusRevenue',
            'ReferralRevenue',
            'TOTAL'
        ];

        if (!$isGodUser && !$SmsStatsPermission) {
            $currencyColumns[] = 'Deductions';
        }

        $reporter
            ->addFilter(new DeductionColumnFilter())
            ->addFilter(new Total(
                $totals,
                ['Revenue', 'Deductions', 'BonusRevenue', 'ReferralRevenue']
            ))
            ->addFilter(new EarningPerClick())
            ->addFilter(new DollarSign($currencyColumns))
            ->addFilter(new UserToolTip())->addFilter(function ($data) {
                foreach ($data as $key => &$row) {
                    if (isset($row['Clicks']) && is_numeric($row['idrep'])) {
                        $queryString = http_build_query(request()->query());
                        $row['Clicks'] = "<a href='/user/{$row['idrep']}/clicks?{$queryString}'>{$row['Clicks']}</a>";
                    }
                }

                return $data;
            });


        return $reporter;
    }

    public function show(Request $request)
    {
        switch (CurrentUserSession::type()) {
            case Privilege::ROLE_GOD:
                $repository = new GodEmployeeRepository(\DB::getPdo());
                break;
            case Privilege::ROLE_ADMIN:
	            $repository = CurrentUserSession::can('view_all_users') ?
		            new GodEmployeeRepository(\DB::getPdo())
		            :
		            new AdminEmployeeRepository(\DB::getPdo());
                break;
            case Privilege::ROLE_MANAGER:
                $repository = new ManagerEmployeeRepository(\DB::getPdo());
                break;
            default:
                abort(400, 'Unknown user.');
        }

	        $dates = self::getDates();
		    ['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
	        $reporter = $this->report($repository, $request);

        return view('report.employee',
	        compact('reporter', 'dates', 'startDate', 'endDate', 'dateSelect'));
    }


}
