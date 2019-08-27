<?php
declare(strict_types=1);

namespace App\Console\Commands;

use DB;
use App\Models\Landing;
use Illuminate\Console\Command;
use App\Models\HourlyStat;
use App\Classes\Statistics;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class LandingRecounter extends Command
{
    protected $signature = 'landing:calc_coefficients {periods}';
    protected $description = 'Calculating coefficients of landings by their statistic.';

    private $date;
    private $yesterday;
    private $yesterday_hours;
    private $today_hours;
    private $month_ago;
    private $today;
    private $week_ago;

    public function handle()
    {
        $periods = $this->argument('periods');
        $periods = explode(',', $periods);

        $landings = Landing::active()->pluck('id')->toArray();

        $this->date = Carbon::now();
        $this->today = $this->date->toDateTimeString();
        $yesterday = clone $this->date->subHours(24);
        $week = clone  $this->date->subDays(6);
        $month = clone $this->date->subMonthsNoOverflow(1);

        $hour = $this->date->format('G');
        $this->yesterday_hours = Statistics::getYesterdayHours($hour);
        $this->today_hours = Statistics::getTodayHours($hour);

        $this->yesterday = $yesterday->toDateString();
        $this->week_ago = $week->toDateTimeString();
        $this->month_ago = $month->toDateTimeString();

        foreach ($landings AS $landing_id) {

            if (\in_array('today', $periods)) {
                $this->calcDailyCoefficients($landing_id);
            }

            if (\in_array('yesterday', $periods)) {
                $this->calcYesterdayCoefficients($landing_id);
            }

            if (\in_array('week', $periods)) {
                $this->calcWeekCoefficients($landing_id);
            }

            if (\in_array('month', $periods)) {
                $this->calcMonthCoefficients($landing_id);
            }
        }
    }

    private function calcDailyCoefficients($landing_id)
    {
        $daily_stat = HourlyStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(transit_landing_hosts) AS transit_landing_hosts'),
            DB::raw('SUM(direct_landing_hosts) AS direct_landing_hosts'),
            DB::raw('SUM(noback_landing_hosts) AS noback_landing_hosts'),
            DB::raw('SUM(comeback_landing_hosts) AS comeback_landing_hosts'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits'))
            ->where('landing_id', $landing_id)
            ->where('datetime', '>=', Carbon::now()->subDay()->toDateTimeString())
            ->groupBy('publisher_id')
            ->get();

        if ($daily_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];
            foreach ($daily_stat AS $by_publisher) {

                $landing_hosts = $this->sumLandingHosts($by_publisher);

                if ($landing_hosts >= (int)config('env.today_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$by_publisher['leads_payout'], (int)$by_publisher['hits']);
                    $cr = Statistics::calculateCr((int)$by_publisher['approved_count'], (int)$by_publisher['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $today_epc = Statistics::getMedianValue($epc_list);
            $today_cr = Statistics::getMedianValue($cr_list);

            Landing::find($landing_id)->update([
                'today_epc' => $today_epc,
                'today_cr' => $today_cr
            ]);
        }
    }

    public function calcYesterdayCoefficients($landing_id)
    {
        $yesterday_stat = HourlyStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(transit_landing_hosts) AS transit_landing_hosts'),
            DB::raw('SUM(direct_landing_hosts) AS direct_landing_hosts'),
            DB::raw('SUM(noback_landing_hosts) AS noback_landing_hosts'),
            DB::raw('SUM(comeback_landing_hosts) AS comeback_landing_hosts'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits')
        )
            ->where(DB::raw("DATE(`datetime`) = '{$this->yesterday}'"))
            ->where('landing_id', $landing_id)
            ->groupBy('publisher_id')
            ->get();


        if ($yesterday_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];

            foreach ($yesterday_stat AS $by_publisher) {
                $landing_hosts = $this->sumLandingHosts($by_publisher);

                if ($landing_hosts >= (int)config('env.yesterday_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$by_publisher['leads_payout'], (int)$by_publisher['hits']);
                    $cr = Statistics::calculateCr((int)$by_publisher['approved_count'], (int)$by_publisher['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $yesterday_epc = Statistics::getMedianValue($epc_list);
            $yesterday_cr = Statistics::getMedianValue($cr_list);

            Landing::find($landing_id)->update([
                'yesterday_epc' => $yesterday_epc,
                'yesterday_cr' => $yesterday_cr
            ]);
        }
    }

    public function calcWeekCoefficients($landing_id)
    {
        $week_stat = HourlyStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(transit_landing_hosts) AS transit_landing_hosts'),
            DB::raw('SUM(direct_landing_hosts) AS direct_landing_hosts'),
            DB::raw('SUM(noback_landing_hosts) AS noback_landing_hosts'),
            DB::raw('SUM(comeback_landing_hosts) AS comeback_landing_hosts'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits'))
            ->where('datetime', '>=', $this->week_ago)
            ->where('landing_id', $landing_id)
            ->groupBy('publisher_id')
            ->get();


        if ($week_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];

            foreach ($week_stat AS $by_publisher) {
                $landing_hosts = $this->sumLandingHosts($by_publisher);

                if ($landing_hosts >= (int)config('env.week_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$by_publisher['leads_payout'], (int)$by_publisher['hits']);
                    $cr = Statistics::calculateCr((int)$by_publisher['approved_count'], (int)$by_publisher['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $week_epc = Statistics::getMedianValue($epc_list);
            $week_cr = Statistics::getMedianValue($cr_list);

            Landing::find($landing_id)->update([
                'week_epc' => $week_epc,
                'week_cr' => $week_cr
            ]);
        }
    }

    public function calcMonthCoefficients($landing_id)
    {
        $month_stat = HourlyStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(transit_landing_hosts) AS transit_landing_hosts'),
            DB::raw('SUM(direct_landing_hosts) AS direct_landing_hosts'),
            DB::raw('SUM(noback_landing_hosts) AS noback_landing_hosts'),
            DB::raw('SUM(comeback_landing_hosts) AS comeback_landing_hosts'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits'))
            ->where('datetime', '>=', $this->month_ago)
            ->where('landing_id', $landing_id)
            ->groupBy('publisher_id')
            ->get();

        if ($month_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];
            foreach ($month_stat AS $by_publisher) {

                $landing_hosts = $this->sumLandingHosts($by_publisher);

                if ($landing_hosts >= (int)config('env.month_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$by_publisher['leads_payout'], (int)$by_publisher['hits']);
                    $cr = Statistics::calculateCr((int)$by_publisher['approved_count'], (int)$by_publisher['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $month_epc = Statistics::getMedianValue($epc_list);
            $month_cr = Statistics::getMedianValue($cr_list);

            Landing::find($landing_id)->update([
                'month_epc' => $month_epc,
                'month_cr' => $month_cr
            ]);
        }
    }

    private function sumLandingHosts($by_publisher): int
    {
        return
            (int)$by_publisher['transit_landing_hosts'] +
            (int)$by_publisher['direct_landing_hosts'] +
            (int)$by_publisher['noback_landing_hosts'] +
            (int)$by_publisher['comeback_landing_hosts'];
    }
}
