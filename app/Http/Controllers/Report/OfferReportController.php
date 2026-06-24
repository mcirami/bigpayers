<?php

namespace App\Http\Controllers\Report;

use App\Privilege;
use App\Offer;
use App\Support\CurrentUserContext;
use App\Support\CurrentUserSession;
use App\Support\LegacyClickLinkFilter as ClickLink;
use App\Support\LegacyDeductionColumnFilter as DeductionColumnFilter;
use App\Support\LegacyDatabaseConnection as DatabaseConnection;
use App\Support\LegacyDollarSignFilter as DollarSign;
use App\Support\LegacyEarningPerClickFilter as EarningPerClick;
use App\Support\LegacyAdminOfferRepository as AdminOfferRepository;
use App\Support\LegacyAffiliateOfferRepository as AffiliateOfferRepository;
use App\Support\LegacyGodOfferRepository as GodOfferRepository;
use App\Support\LegacyManagerOfferRepository as ManagerOfferRepository;
use App\Support\LegacyReporter as Reporter;
use App\Support\LegacyTotalFilter as Total;
use App\Support\RequestContext;
use App\Services\CountryReportBuilderService;
use App\Services\Repositories\Offer\OfferAffiliateClicksRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OfferReportController extends ReportController
{
	private const array OFFER_TOTAL_COLUMNS = [
		'Clicks',
		'UniqueClicks',
		'FreeSignUps',
		'PendingConversions',
		'Conversions',
		'Revenue',
		'Deductions',
	];

    private function god() {
        $dates = self::getDates();
        $repo = new GodOfferRepository(\DB::getPdo());

        $reporter = new Reporter($repo);
	    ['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);

		$this->applyAdminStyleOfferFilters($reporter);

        return view('report.offer.admin',
		        compact('reporter', 'dates', 'startDate', 'endDate', 'dateSelect'));
    }

    private function admin()
    {
        return $this->adminReport(CurrentUserSession::snapshot());
    }

    private function adminReport(CurrentUserContext $currentUserContext)
    {
        $dates = self::getDates();
	    $repo = $currentUserContext->can('view_all_users') ?
		    new GodOfferRepository(\DB::getPdo())
		    :
		    new AdminOfferRepository(\DB::getPdo());

        $reporter = new Reporter($repo);
	    ['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);

		$this->applyAdminStyleOfferFilters($reporter);

        return view('report.offer.admin',
		        compact('reporter', 'dates', 'startDate', 'endDate', 'dateSelect'));
    }

    private function manager()
    {
        $dates = self::getDates();
        $repo = new ManagerOfferRepository(DatabaseConnection::getInstance());

        $reporter = new Reporter($repo);

		$this->applyAdminStyleOfferFilters($reporter);

        return view('report.offer.admin', compact('reporter', 'dates'));
    }

    private function affiliate()
    {
        return $this->affiliateReport(CurrentUserSession::snapshot());
    }

    private function affiliateReport(CurrentUserContext $currentUserContext)
    {
        $dates = self::getDates();
        $bonusRows = DB::table('click_bonus')
            ->join('bonus', 'bonus.id', '=', 'click_bonus.bonus_id')
            ->where('click_bonus.aff_id', '=', $currentUserContext->id)
            ->whereBetween('click_bonus.timestamp', [
                Carbon::createFromFormat('Y-m-d H:i:s', $dates['startDate'])->timestamp,
                Carbon::createFromFormat('Y-m-d H:i:s', $dates['endDate'])->timestamp,
            ])
            ->get(['bonus.name', 'click_bonus.payout']);

        $repo = new AffiliateOfferRepository(\DB::getPdo());
        $repo->setAffiliateId($currentUserContext->id);

        $reporter = new Reporter($repo);

		$this->applyAffiliateOfferFilters($reporter);

        if (RequestContext::expectsJson()) {
            return response($reporter->fetchReport($dates['startDate'], $dates['endDate']));
        }

        return view('report.offer.affiliate', compact('reporter', 'bonusRows', 'dates'));
    }

    public function show()
    {
        $currentUserContext = CurrentUserSession::snapshot();

        switch ($currentUserContext->type) {
            case Privilege::ROLE_GOD:
                return $this->god();
                
            case Privilege::ROLE_ADMIN:
                return $this->adminReport($currentUserContext);

            case Privilege::ROLE_MANAGER:
                return $this->manager();

            case Privilege::ROLE_AFFILIATE:
                return $this->affiliateReport($currentUserContext);

            default:
                return redirect('/');
        }
    }

	public function showConversionsByUser(Offer $offer)
	{
        $currentUserContext = CurrentUserSession::snapshot();
		$dates = self::getDates();
		//$offer = Offer::findOrFail($offerId);

		$start = Carbon::parse($dates['startDate'], 'America/New_York');
		$end = Carbon::parse($dates['endDate'], 'America/New_York');

		$affiliateRepo = new OfferAffiliateClicksRepository($offer->idoffer, $currentUserContext->user());
		$affiliateReport = $affiliateRepo->between($start, $end);

		return view('report.offer.conversions', compact('affiliateReport', 'offer'));
	}

	public function showConversionsByCountry(Offer $offer, CountryReportBuilderService $countryReportBuilderService)
	{
        $currentUserContext = CurrentUserSession::snapshot();
		$dates = self::getDates();

		$start = Carbon::parse($dates['startDate'], 'America/New_York');
		$end = Carbon::parse($dates['endDate'], 'America/New_York');

		$affiliateRepo = new OfferAffiliateClicksRepository($offer->idoffer, $currentUserContext->user());
		$affiliateReport = $affiliateRepo->getOfferConversionsByCountry($countryReportBuilderService, $start, $end);

		return view('report.offer.conversions-by-country', compact('affiliateReport', 'offer'));
	}

	private function applyAdminStyleOfferFilters(Reporter $reporter): void {
		$reporter
			->addFilter( new DeductionColumnFilter() )
			->addFilter( new Total( self::OFFER_TOTAL_COLUMNS ) )
			->addFilter( new EarningPerClick( 'UniqueClicks', 'Revenue' ) )
			->addFilter( new DollarSign( [ 'Revenue', 'Deductions', 'EPC' ] ) )
			->addFilter( new ClickLink( RequestContext::current() ) );
	}

	private function applyAffiliateOfferFilters(Reporter $reporter): void {
		$reporter
			->addFilter( new DeductionColumnFilter() )
			->addFilter( new Total(
				array_merge( self::OFFER_TOTAL_COLUMNS, [ 'TOTAL' ] ),
				[ 'Revenue', 'Deductions' ]
			) )
			->addFilter( new EarningPerClick( 'UniqueClicks', 'Revenue' ) )
			->addFilter( new DollarSign( [ 'Revenue', 'Deductions', 'EPC', 'TOTAL' ] ) );
	}
}
