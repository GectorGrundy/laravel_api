<?php
declare(strict_types=1);

namespace App\Console\Commands;

use DB;
use Carbon\Carbon;
use App\Models\Transit;
use Illuminate\Console\Command;
use App\Models\HourlyStat;
use App\Classes\Statistics;
use Illuminate\Database\Eloquent\Builder;

class TransitRecounter extends Command
{
    protected $signature = 'transit:calc_coefficients {periods}';
    protected $description = 'Calculating coefficients of transits by their statistic.';

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

        $transits = Transit::active()->pluck('id')->toArray();

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

        foreach ($transits AS $transit_id) {

            if (\in_array('today', $periods)) {
                $this->calcDailyCoefficients($transit_id);
            }

            if (\in_array('yesterday', $periods)) {
                $this->calcYesterdayCoefficients($transit_id);
            }

            if (\in_array('week', $periods)) {
                $this->calcWeekCoefficients($transit_id);
            }

            if (\in_array('month', $periods)) {
                $this->calcMonthCoefficients($transit_id);
            }
        }
    }

    private function calcDailyCoefficients($transit_id)
    {
        $daily_stat = HourlyStat::select(
            DB::raw('SUM(transit_hosts) AS transit_hosts'),
            DB::raw('SUM(transit_landing_hosts) AS transit_landing_hosts')
        )
            ->where('transit_id', $transit_id)
            ->where('datetime', '>=', Carbon::now()->subDay()->toDateTimeString())
            ->groupBy('publisher_id')
            ->get();

        if ($daily_stat->count() > 0) {

            $ctr_list = [];
            foreach ($daily_stat AS $by_publisher) {

                if ($by_publisher['transit_hosts'] >= (int)config('env.today_reliable_hosts', 0)) {

                    $ctr = Statistics::calculateCtr(
                        (int)$by_publisher['transit_hosts'],
                        (int)$by_publisher['transit_landing_hosts']
                    );

                    $ctr_list[] = $ctr;
                }
            }

            $today_ctr = Statistics::getMedianValue($ctr_list);

            Transit::find($transit_id)->update([
                'today_ctr' => $today_ctr
            ]);
        }
    }

    public function calcYesterdayCoefficients($transit_id)
    {
        $yesterday_stat = HourlyStat::select(
            DB::raw('SUM(transit_hosts) AS transit_hosts'),
            DB::raw('SUM(transit_landing_hosts) AS transit_landing_hosts')
        )
            ->where(DB::raw("DATE(`datetime`) = '{$this->yesterday}'"))
            ->where('transit_id', $transit_id)
            ->groupBy('publisher_id')
            ->get();


        if ($yesterday_stat->count() > 0) {

            $ctr_list = [];
            foreach ($yesterday_stat AS $by_publisher) {

                if ($by_publisher['transit_hosts'] >= (int)config('env.yesterday_reliable_hosts', 0)) {

                    $ctr = Statistics::calculateCtr(
                        (int)$by_publisher['transit_hosts'],
                        (int)$by_publisher['transit_landing_hosts']
                    );

                    $ctr_list[] = $ctr;
                }
            }

            $yesterday_ctr = Statistics::getMedianValue($ctr_list);

            Transit::find($transit_id)->update([
                'yesterday_ctr' => $yesterday_ctr
            ]);
        }
    }

    public function calcWeekCoefficients($transit_id)
    {
        $week_stat = HourlyStat::select(
            DB::raw('SUM(transit_hosts) AS transit_hosts'),
            DB::raw('SUM(transit_landing_hosts) AS transit_landing_hosts')
        )
            ->where('datetime', '>=', $this->week_ago)
            ->where('transit_id', $transit_id)
            ->groupBy('publisher_id')
            ->get();


        if ($week_stat->count() > 0) {

            $ctr_list = [];
            foreach ($week_stat AS $by_publisher) {

                if ($by_publisher['transit_hosts'] >= (int)config('env.week_reliable_hosts', 0)) {

                    $ctr = Statistics::calculateCtr(
                        (int)$by_publisher['transit_hosts'],
                        (int)$by_publisher['transit_landing_hosts']
                    );

                    $ctr_list[] = $ctr;
                }
            }

            $week_ctr = Statistics::getMedianValue($ctr_list);

            Transit::find($transit_id)->update([
                'week_ctr' => $week_ctr,
            ]);
        }
    }

    public function calcMonthCoefficients($transit_id)
    {
        $month_stat = HourlyStat::select(
            DB::raw('SUM(transit_hosts) AS transit_hosts'),
            DB::raw('SUM(transit_landing_hosts) AS transit_landing_hosts')
        )
            ->where('datetime', '>=', $this->month_ago)
            ->where('transit_id', $transit_id)
            ->groupBy('publisher_id')
            ->get();

        if ($month_stat->count() > 0) {

            $ctr_list = [];
            foreach ($month_stat AS $by_publisher) {

                if ($by_publisher['transit_hosts'] >= (int)config('env.month_reliable_hosts', 0)) {

                    $ctr = Statistics::calculateCtr(
                        (int)$by_publisher['transit_hosts'],
                        (int)$by_publisher['transit_landing_hosts']
                    );

                    $ctr_list[] = $ctr;
                }
            }

            $month_ctr = Statistics::getMedianValue($ctr_list);

            Transit::find($transit_id)->update([
                'month_ctr' => $month_ctr
            ]);
        }
    }
}
