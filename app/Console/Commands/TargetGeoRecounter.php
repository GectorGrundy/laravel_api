<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\TargetGeo;
use App\Models\TargetGeoStat;
use App\Models\UserGroupTargetGeo;
use DB;
use Carbon\Carbon;
use App\Classes\Statistics;
use Illuminate\Console\Command;

class TargetGeoRecounter extends Command
{
    protected $signature = 'target_geo:calc_coefficients {periods}';
    protected $description = 'Calculating coefficients of targe geo by their statistic.';

    private $date;
    private $yesterday;
    private $yesterday_hours;
    private $today;
    private $today_hours;
    private $month_ago;
    private $week_ago;

    public function handle()
    {
        $periods = $this->argument('periods');
        $periods = explode(',', $periods);

        $target_geo = TargetGeo::active()->pluck('id')->toArray();

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

        foreach ($target_geo AS $target_geo_id) {

            if (\in_array('today', $periods)) {
                $this->calcDailyCoefficients($target_geo_id);
            }

            if (\in_array('yesterday', $periods)) {
                $this->calcYesterdayCoefficients($target_geo_id);
            }

            if (\in_array('week', $periods)) {
                $this->calcWeekCoefficients($target_geo_id);
            }

            if (\in_array('month', $periods)) {
                $this->calcMonthCoefficients($target_geo_id);
            }
        }
    }

    private function calcDailyCoefficients($target_geo_id)
    {
        $daily_stat = TargetGeoStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits'))
            ->where('target_geo_id', $target_geo_id)
            ->where('datetime', '>=', Carbon::now()->subDay()->toDateTimeString())
            ->get();

        if ($daily_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];
            foreach ($daily_stat AS $stat) {
                if ($stat['flow_hosts'] >= (int)config('env.today_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$stat['leads_payout'], (int)$stat['hits']);
                    $cr = Statistics::calculateCr((int)$stat['approved_count'], (int)$stat['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $today_epc = Statistics::getMedianValue($epc_list);
            $today_cr = Statistics::getMedianValue($cr_list);

            TargetGeo::find($target_geo_id)->update([
                'today_epc' => $today_epc,
                'today_cr' => $today_cr
            ]);

            UserGroupTargetGeo::where('target_geo_id', $target_geo_id)->update([
                'today_epc' => $today_epc,
                'today_cr' => $today_cr
            ]);
        }
    }

    public function calcYesterdayCoefficients($target_geo_id)
    {
        $yesterday_stat = TargetGeoStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits'))
            ->where(DB::raw("DATE(`datetime`) = '{$this->yesterday}'"))
            ->where('target_geo_id', $target_geo_id)
            ->get();

        if ($yesterday_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];

            foreach ($yesterday_stat AS $stat) {

                if ($stat['flow_hosts'] >= (int)config('env.yesterday_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$stat['leads_payout'], (int)$stat['hits']);
                    $cr = Statistics::calculateCr((int)$stat['approved_count'], (int)$stat['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $yesterday_epc = Statistics::getMedianValue($epc_list);
            $yesterday_cr = Statistics::getMedianValue($cr_list);

            TargetGeo::find($target_geo_id)->update([
                'yesterday_epc' => $yesterday_epc,
                'yesterday_cr' => $yesterday_cr
            ]);

            UserGroupTargetGeo::where('target_geo_id', $target_geo_id)->update([
                'today_epc' => $yesterday_epc,
                'today_cr' => $yesterday_cr
            ]);
        }
    }

    public function calcWeekCoefficients($target_geo_id)
    {
        $week_stat = TargetGeoStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits'))
            ->where('datetime', '>=', $this->week_ago)
            ->where('target_geo_id', $target_geo_id)
            ->get();

        if ($week_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];
            foreach ($week_stat AS $stat) {

                if ($stat['fllow_hosts'] >= (int)config('env.week_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$stat['leads_payout'], (int)$stat['hits']);
                    $cr = Statistics::calculateCr((int)$stat['approved_count'], (int)$stat['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $week_epc = Statistics::getMedianValue($epc_list);
            $week_cr = Statistics::getMedianValue($cr_list);

            TargetGeo::find($target_geo_id)->update([
                'week_epc' => $week_epc,
                'week_cr' => $week_cr
            ]);

            UserGroupTargetGeo::where('target_geo_id', $target_geo_id)->update([
                'today_epc' => $week_epc,
                'today_cr' => $week_cr
            ]);
        }
    }

    public function calcMonthCoefficients($target_geo_id)
    {
        $month_stat = TargetGeoStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits'))
            ->where('datetime', '>=', $this->month_ago)
            ->where('target_geo_id', $target_geo_id)
            ->get();
        if ($month_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];
            foreach ($month_stat AS $stat) {

                if ($stat['flow_hosts'] >= (int)config('env.month_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$stat['leads_payout'], (int)$stat['hits']);
                    $cr = Statistics::calculateCr((int)$stat['approved_count'], (int)$stat['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $month_epc = Statistics::getMedianValue($epc_list);
            $month_cr = Statistics::getMedianValue($cr_list);

            TargetGeo::find($target_geo_id)->update([
                'month_epc' => $month_epc,
                'month_cr' => $month_cr
            ]);

            UserGroupTargetGeo::where('target_geo_id', $target_geo_id)->update([
                'today_epc' => $month_epc,
                'today_cr' => $month_cr
            ]);
        }

    }
}
