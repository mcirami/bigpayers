<?php

namespace App\Http\Controllers;

use App\Click;
use App\Exports\ClicksExport;
use App\Exports\OfferDataExport;
use App\Exports\AffDataExport;
use App\Exports\CountryClicksExport;
use App\Privilege;
use App\Http\Controllers\Report\ReportController;
use App\Support\Report\Repositories\Employee\GodEmployeeRepository;
use App\Support\Report\Repositories\Offer\GodOfferRepository;
use App\Support\RequestContext;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\ClickTraits;
use PhpOffice\PhpSpreadsheet\Exception;
use App\Services\ClickGeoCacheService;
class ExportDataController extends ReportController
{
	use ClickTraits;

	/**
	 * @throws Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function exportUsersClicks($userId) {

		$dates = self::getDates();
		$selectedRole = (int) RequestContext::query('role', Privilege::ROLE_AFFILIATE);

		$reportCollection = Click::query()
			->userClicksReportByRole($userId, $dates['startDate'], $dates['endDate'], $selectedRole)
			->get();
		$report = $this->formatResults($reportCollection);
		return Excel::download(new ClicksExport($report), 'clicks.xlsx');
	}

	/**
	 * @throws Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function exportOfferData() {
		$dates = self::getDates();
		$repo = new GodOfferRepository(\DB::getPdo());
		$data = $repo->between($dates['startDate'], $dates['endDate']);

		return Excel::download(new OfferDataExport($data), 'offer-data.xlsx');
	}

	/**
	 * @throws Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function exportAffData() {
		$dates = self::getDates();
		$repository = new GodEmployeeRepository(\DB::getPdo());
		$repository->SHOW_AFF_TYPE = RequestContext::query('role', 3);
		$data = $repository->between($dates['startDate'], $dates['endDate']);

		return Excel::download(new AffDataExport($data), 'Aff-data.xlsx');
	}

	/**
	 * @throws Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function exportCountryClicks(ClickGeoCacheService $geoCache) {
		$dates = self::getDates();
		$geoCode = RequestContext::query('country');

		$ips = Click::missingCountryCodeIps($dates['startDate'], $dates['endDate']);

		$geoCache->warm($ips);

		$report = Click::query()
		               ->countryClicksInGeo($dates['startDate'], $dates['endDate'], $geoCode)
		               ->get();

		return Excel::download(new CountryClicksExport($report), 'country-clicks.xlsx');
	}
}
