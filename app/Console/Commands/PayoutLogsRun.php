<?php

namespace App\Console\Commands;

use App\Company;
use App\PayoutLog;
use App\Services\CompanyDatabaseConnectionManager;
use App\Support\LegacyAdminEmployeeRepository as AdminEmployeeRepository;
use App\Support\DateHelper as Date;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PayoutLogsRun extends Command
{
    private $connections;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payout-logs:run {--start=} {--end=} {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Logs current payouts to a table for easier querying.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CompanyDatabaseConnectionManager $connections)
    {
        parent::__construct();

        $this->connections = $connections;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $startTime = Carbon::now();
        foreach (Company::all() as $company) {
            $currentStartTime = Carbon::now();
            $this->info('Running payout logs for company: ' . $company->subDomain);

            $this->connections->useAsDefault($company);

            if ($this->option('start') && $this->option('end')) {
                $start = Carbon::parse($this->option('start'));
                $end = Carbon::parse($this->option('end'));


                if ($start->dayOfWeek != Carbon::MONDAY || $end->dayOfWeek != Carbon::SUNDAY) {
                    $this->warn('Date range must start with Monday and end on Sunday');
                    return false;
                }
            } else {
                $start = Carbon::now()->subWeek()->startOfWeek();
                $end = Carbon::now()->subWeek()->endOfWeek();
            }

            if ($this->option('truncate')) {
                PayoutLog::query()->delete();
                $this->info('Truncated payout_logs table.');
            }

            for ($weekCount = $end->diffInWeeks($start) + 1; $weekCount > 0; $weekCount--) {
//                $this->call('payout-logs:run', ['--start' => $start->startOfWeek(), '--end' => $start->endOfWeek()]);
                $repository = new AdminEmployeeRepository(\DB::getPdo());
                $originalStart = $startOfWeek = $start->startOfWeek()->format('Y-m-d');
                $originalEnd = $endOfWeek = $start->endOfWeek()->format('Y-m-d');
                Date::addHis($startOfWeek, $endOfWeek);
                $startOfWeek = Date::convertDateTimezone($startOfWeek);
                $endOfWeek = Date::convertDateTimezone($endOfWeek);

                // Delete the current date range (if any)
                PayoutLog::where([
                    ['start_of_week', '>=', $originalStart],
                    ['end_of_week', '<=', $originalEnd]
                ])->delete();

                // Get current weeks report
                $report = $repository->between($startOfWeek, $endOfWeek);


                // Log current week
                $this->info('Week through ' . $startOfWeek . ' - ' . $endOfWeek);
                foreach ($report as $row) {
                    $log = PayoutLog::create([
                        'user_id' => $row['idrep'],
                        'revenue' => $row["Revenue"],
                        'deductions' => $row['Deductions'],
                        'bonuses' => $row['BonusRevenue'],
                        'referrals' => $row['ReferralRevenue'],
                        'start_of_week' => $originalStart,
                        'end_of_week' => $originalEnd
                    ]);
                    $this->info($log->user_id);

                }

                $start->addWeek();
            }

            $this->info('Finished ' . $company->subDomain . ' in ' . $currentStartTime->diffInRealSeconds() . ' seconds.');
        }

        $this->info('Completed in ' . $startTime->diffInRealSeconds() . ' seconds.');
    }
}
