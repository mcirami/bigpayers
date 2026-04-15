<?php

namespace App\Http\Controllers\Report;

use App\Privilege;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\Report\Filters;
use LeadMax\TrackYourStats\Report\Repositories\Employee\GodEmployeeRepository;
use LeadMax\TrackYourStats\Report\Repositories\Employee\AdminEmployeeRepository;
use LeadMax\TrackYourStats\Report\Repositories\Employee\ManagerEmployeeRepository;
use LeadMax\TrackYourStats\Report\Repositories\Repository;
use LeadMax\TrackYourStats\System\Session;

class EmployeeReportController extends ReportController
{


    private function report(Repository $repository, Request $request)
    {
        $repository->SHOW_AFF_TYPE = $request->query('role', 3);
        $isGodUser = Session::userType() === Privilege::ROLE_GOD;
	    $SmsStatsPermission = Session::permissions()->can('view_sms_stats');

        $reporter = new \LeadMax\TrackYourStats\Report\Reporter($repository);

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
            ->addFilter(new Filters\DeductionColumnFilter())
            ->addFilter(new Filters\Total(
                $totals,
                ['Revenue', 'Deductions', 'BonusRevenue', 'ReferralRevenue']
            ))
            ->addFilter(new Filters\EarningPerClick())
            ->addFilter(new Filters\DollarSign($currencyColumns))
            ->addFilter(new Filters\UserToolTip())->addFilter(function ($data) {
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
        switch (Session::userType()) {
            case Privilege::ROLE_GOD:
                $repository = new GodEmployeeRepository(\DB::getPdo());
                break;
            case Privilege::ROLE_ADMIN:
	            $repository = Session::permissions()->can('view_all_users') ?
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
