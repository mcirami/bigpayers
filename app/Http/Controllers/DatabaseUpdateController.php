<?php

namespace App\Http\Controllers;

use App\Support\LegacyCompanyUpdater as CompanyUpdater;
use Illuminate\Http\Request;
use Throwable;

class DatabaseUpdateController extends Controller
{
    public function index()
    {
        return view('admin.database-updates', $this->buildViewData(false));
    }

    public function run(Request $request)
    {
        return view('admin.database-updates', $this->buildViewData(true));
    }

    private function buildViewData(bool $didRun): array
    {
        $report = [];
        $error = null;

        try {
            $updater = new CompanyUpdater();
            $rawReport = $didRun ? $updater->updateCompanies() : $updater->findRequiredUpdates();
            $report = $this->normalizeReport($rawReport);
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }

        return [
            'pageTitle' => 'Database Updates',
            'didRun' => $didRun,
            'error' => $error,
            'report' => $report,
            'totalCompanies' => count($report),
            'totalRequired' => array_sum(array_column($report, 'requiredCount')),
            'totalSuccess' => array_sum(array_column($report, 'successCount')),
            'totalFailure' => array_sum(array_column($report, 'failureCount')),
        ];
    }

    private function normalizeReport(array $rawReport): array
    {
        $report = [];

        foreach ($rawReport as $subDomain => $companyReport) {
            $required = $this->normalizeVersions($companyReport['required_updates'] ?? []);
            $valid = $this->normalizeVersions($companyReport['valid_updates'] ?? []);
            $success = $this->normalizeClassNames($companyReport['success'] ?? []);
            $failure = $this->normalizeClassNames($companyReport['failure'] ?? []);

            $report[] = [
                'subDomain' => $subDomain,
                'required' => $required,
                'valid' => $valid,
                'success' => $success,
                'failure' => $failure,
                'requiredCount' => count($required),
                'validCount' => count($valid),
                'successCount' => count($success),
                'failureCount' => count($failure),
                'latestVersion' => isset($companyReport['updater']) ? $companyReport['updater']->getLatestVersion() : null,
            ];
        }

        return $report;
    }

    private function normalizeVersions(array $versions): array
    {
        return array_map(function ($version) {
            if (is_object($version)) {
                return [
                    'name' => class_basename(get_class($version)),
                    'version' => method_exists($version, 'getVersion') ? $version->getVersion() : null,
                ];
            }

            return [
                'name' => class_basename((string) $version),
                'version' => null,
            ];
        }, $versions);
    }

    private function normalizeClassNames(array $classNames): array
    {
        return array_map(fn ($className) => class_basename((string) $className), $classNames);
    }
}
