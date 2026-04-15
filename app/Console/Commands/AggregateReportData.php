<?php

namespace App\Console\Commands;

use App\AggregateReport;
use App\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use LeadMax\TrackYourStats\Report\Repositories\Employee\AdminEmployeeRepository;
use LeadMax\TrackYourStats\Table\Date;

class AggregateReportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:aggregate {--start=} {--end=} {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregates report data per user with the given dates (Yesterday is default).';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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
            $this->info('Aggregating for : ' . $company->subDomain);

            // TODO: This _should_? be put into a "Service" class, or a facade of sorts..
            \Config::set('database.connections.' . $company->subDomain, array(
                'driver' => 'mysql',
                'host' => env('DB_HOST'),
                'database' => $company->subDomain,
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
            ));
            \DB::setDefaultConnection($company->subDomain);


            if ($this->option('start') && $this->option('end')) {
                $start = Carbon::parse($this->option('start'));
                $end = Carbon::parse($this->option('end'));
            } else {
                $start = Carbon::yesterday();
                $end = Carbon::yesterday();
            }

            if ($this->option('truncate')) {
                AggregateReport::query()->delete();
                $this->error('Truncated aggregated_reports table.');
            }

            for ($days = $start->diffInDays($end) + 1; $days > 0; $days--) {
                $startDate = $endDate = $start->format('Y-m-d');
                Date::addHis($startDate, $endDate);
                $startDate = Date::convertDateTimezone($startDate);
                $endDate = Date::convertDateTimezone($endDate);
                $this->warn($startDate . ' -- ' . $endDate);
                $repository = new AdminEmployeeRepository(\DB::getPdo());

                AggregateReport::where('aggregate_date', $start->format('Y-m-d'))->delete();

                $report = $repository->between($startDate, $endDate);

                $this->info('Aggregating data on ' . $start->format('Y-m-d'));
                $totals = [
                    'users' => 0,
                    'clicks' => 0,
                    'unique_clicks' => 0,
                    'free_sign_ups' => 0,
                    'pending_conversions' => 0,
                    'conversions' => 0,
                    'revenue' => 0,
                    'deductions' => 0
                ];
                foreach ($report as $row) {
                    AggregateReport::create([
                        'user_id' => $row['idrep'],
                        'clicks' => $row['Clicks'],
                        'unique_clicks' => $row['UniqueClicks'],
                        'free_sign_ups' => $row['FreeSignUps'],
                        'pending_conversions' => $row['PendingConversions'],
                        'conversions' => $row['Conversions'],
                        'revenue' => $row['Revenue'],
                        'deductions' => $row['Deductions'],
                        'aggregate_date' => $start->format('Y-m-d')
                    ]);
                    $totals['users']++;
                    $totals['clicks'] += $row['Clicks'];
                    $totals['unique_clicks'] += $row['UniqueClicks'];
                    $totals['free_sign_ups'] += $row['FreeSignUps'];
                    $totals['pending_conversions'] += $row['PendingConversions'];
                    $totals['conversions'] += $row['Conversions'];
                    $totals['revenue'] += $row['Revenue'];
                    $totals['deductions'] += $row['Deductions'];
                }
                $str = '';
                foreach ($totals as $key => $val) {
                    $str .= $key . ': ' . $val . ' | ';
                }
                $this->line($str);


                $start->addDay();
            }

            $this->info('Finished ' . $company->subDomain . ' ' . $currentStartTime->diffForHumans());
        }
        $this->info('Completed ' . $startTime->diffForHumans());
    }
}
