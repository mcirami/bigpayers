<?php

namespace App\Http\Controllers\Report;

use App\Support\LegacyDollarSignFilter as DollarSign;
use App\Support\LegacyEarningPerClickFilter as EarningPerClick;
use Carbon\Carbon;
use App\Support\LegacyReporter as Reporter;
use Illuminate\Http\Request;
use App\Support\LegacyTotalFilter as Total;
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
