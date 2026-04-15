<?php

namespace App\Http\Controllers\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\Report\Filters\DollarSign;
use LeadMax\TrackYourStats\Report\Filters\EarningPerClick;
use LeadMax\TrackYourStats\Report\Filters\Total;
use LeadMax\TrackYourStats\Report\Reporter;
use LeadMax\TrackYourStats\Report\Repositories\AdvertiserRepository;

class AdvertiserReportController extends ReportController
{



    public function show()
    {
        $dates = self::getDates();
        $repository = new AdvertiserRepository(\DB::getPdo());
        $reporter = new Reporter($repository);
        $reporter->addFilter(new Total(['Clicks', 'UniqueClicks', 'PendingConversions', 'FreeSignUps', 'Conversions', 'Revenue', 'TOTAL'], ['Revenue']))
            ->addFilter(new EarningPerClick())
            ->addFilter(new DollarSign(['Revenue', 'Deductions','TOTAL']));

        return view('report.advertiser', compact('reporter', 'dates'));
    }

	public function showConversionsByOffer($id) {
		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$repository = new AdvertiserRepository(\DB::getPdo());
		$affiliateReport = $repository->getAdvConversionsByOffer($id, $dates['startDate'], $dates['endDate']);

		return view('report.advertiser-offer-conversions',
			compact(
				'startDate',
				'endDate',
				'dateSelect',
				'affiliateReport'
			));
	}

}
