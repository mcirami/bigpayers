<?php

namespace App\Http\Controllers;

use App\Privilege;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\Clicks\ClickGeo;
use LeadMax\TrackYourStats\Clicks\ClickSearcher;
use LeadMax\TrackYourStats\Clicks\Conversion;
use LeadMax\TrackYourStats\Clicks\UID;
use LeadMax\TrackYourStats\System\Session;
use PDO;

class ClickSearchController extends Controller
{
    public function show(Request $request)
    {
        $this->ensureGodAccess();

        $searchValue = trim((string) $request->query('clickId', ''));
        $searchAttempted = $request->filled('clickId');
        $decodedClickId = null;
        $lookupMode = null;
        $clickRow = null;
        $geoData = [];
        $queryVars = [];
        $conversion = null;
        $encodedAlias = null;

        if ($searchAttempted) {
            $lookupMode = is_numeric($searchValue) ? 'Numeric' : 'Encoded';
            $decodedClickId = is_numeric($searchValue) ? $searchValue : UID::decode($searchValue);

            $clickSearcher = new ClickSearcher($decodedClickId);
            $clickRow = $clickSearcher->clickData()->fetch(PDO::FETCH_ASSOC) ?: null;

            $storedQueryVars = $clickSearcher->clickVars()->fetch(PDO::FETCH_ASSOC) ?: [];
            $queryVars = $this->formatQueryVars($storedQueryVars);

            if ($clickRow) {
                $geoData = ClickGeo::findGeo((string) ($clickRow['ip_address'] ?? ''));
                $conversion = Conversion::selectOne($decodedClickId)->fetch(PDO::FETCH_ASSOC) ?: null;
                $encodedAlias = ctype_digit((string) $decodedClickId) ? UID::encode((string) $decodedClickId) : null;
            }
        }

        return view('tools.click-search', [
            'searchValue' => $searchValue,
            'searchAttempted' => $searchAttempted,
            'lookupMode' => $lookupMode,
            'decodedClickId' => $decodedClickId,
            'encodedAlias' => $encodedAlias,
            'clickFound' => $clickRow !== null,
            'clickData' => $this->formatClickData($clickRow),
            'geoData' => $this->formatGeoData($geoData),
            'queryVars' => $queryVars,
            'conversionData' => $this->formatConversionData($conversion),
            'queryVarCount' => collect($queryVars)->filter(fn ($value) => $value !== '—')->count(),
        ]);
    }

    private function ensureGodAccess(): void
    {
        abort_unless(Session::userType() === Privilege::ROLE_GOD, 403, 'Incorrect user type');
    }

    private function formatClickData(?array $clickRow): array
    {
        if (!$clickRow) {
            return [];
        }

        return [
            'ID' => $clickRow['idclicks'] ?? '—',
            'Time' => $clickRow['first_timestamp'] ?? '—',
            'User' => $clickRow['rep_idrep'] ?? '—',
            'Offer' => $clickRow['offer_idoffer'] ?? '—',
            'IP' => $clickRow['ip_address'] ?? '—',
            'Agent' => $clickRow['browser_agent'] ?? '—',
            'Type' => $clickRow['click_type'] ?? '—',
        ];
    }

    private function formatGeoData(array $geoData): array
    {
        if (!$geoData) {
            return [];
        }

        return [
            'ISO' => $this->displayValue($geoData['isoCode'] ?? null),
            'Region' => $this->displayValue($geoData['subDivision'] ?? null),
            'City' => $this->displayValue($geoData['city'] ?? null),
            'Postal' => $this->displayValue($geoData['postal'] ?? null),
            'Latitude' => $this->displayValue($geoData['latitude'] ?? null),
            'Longitude' => $this->displayValue($geoData['longitude'] ?? null),
        ];
    }

    private function formatQueryVars(array $storedQueryVars): array
    {
        $orderedKeys = [
            'url' => 'URL',
            'sub1' => 'Sub 1',
            'sub2' => 'Sub 2',
            'sub3' => 'Sub 3',
            'sub4' => 'Sub 4',
            'sub5' => 'Sub 5',
        ];

        $output = [];

        foreach ($orderedKeys as $key => $label) {
            $output[$label] = $this->displayValue($storedQueryVars[$key] ?? null);
        }

        return $output;
    }

    private function formatConversionData(?array $conversion): array
    {
        if (!$conversion) {
            return [];
        }

        return [
            'ID' => $conversion['id'] ?? '—',
            'Time' => $conversion['timestamp'] ?? '—',
            'Paid' => $conversion['paid'] ?? '—',
        ];
    }

    private function displayValue($value): string
    {
        $string = trim((string) $value);

        return $string !== '' ? $string : '—';
    }
}
