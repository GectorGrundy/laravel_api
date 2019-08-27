<?php
declare(strict_types=1);

namespace App\Console\Commands;

use DB;
use Carbon\Carbon;
use App\Models\Offer;
use App\Models\HourlyStat;
use App\Classes\Statistics;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class OfferRecounter extends Command
{
    protected $signature = 'offer:calc_coefficients {periods}';
    protected $description = 'Calculating coefficients of offers by their statistic.';

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

        $offers = Offer::active()->pluck('id')->toArray();

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

        foreach ($offers AS $offer_id) {

            if (\in_array('today', $periods)) {
                $this->calcDailyCoefficients($offer_id);
            }

            if (\in_array('yesterday', $periods)) {
                $this->calcYesterdayCoefficients($offer_id);
            }

            if (\in_array('week', $periods)) {
                $this->calcWeekCoefficients($offer_id);
            }

            if (\in_array('month', $periods)) {
                $this->calcMonthCoefficients($offer_id);
            }
        }
    }

    private function calcDailyCoefficients($offer_id)
    {
        $daily_stat = HourlyStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(offer_hosts) AS offer_hosts'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits'))
            ->where('offer_id', $offer_id)
            ->where('datetime', '>=', Carbon::now()->subDay()->toDateTimeString())
            ->groupBy('publisher_id')
            ->get();

        if ($daily_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];
            foreach ($daily_stat AS $stat) {

                if ($stat['offer_hosts'] >= (int)config('env.today_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$stat['leads_payout'], (int)$stat['hits']);
                    $cr = Statistics::calculateCr((int)$stat['approved_count'], (int)$stat['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $today_epc = Statistics::getMedianValue($epc_list);
            $today_cr = Statistics::getMedianValue($cr_list);

            Offer::find($offer_id)->update([
                'today_epc' => $today_epc,
                'today_cr' => $today_cr
            ]);
        }
    }

    public function calcYesterdayCoefficients($offer_id)
    {
        $yesterday_stat = HourlyStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(offer_hosts) AS offer_hosts'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits')
        )
            ->where(DB::raw("DATE(`datetime`) = '{$this->yesterday}'"))
            ->where('offer_id', $offer_id)
            ->groupBy('publisher_id')
            ->get();


        if ($yesterday_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];

            foreach ($yesterday_stat AS $stat) {

                if ($stat['offer_hosts'] >= (int)config('env.yesterday_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$stat['leads_payout'], (int)$stat['hits']);
                    $cr = Statistics::calculateCr((int)$stat['approved_count'], (int)$stat['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $yesterday_epc = Statistics::getMedianValue($epc_list);
            $yesterday_cr = Statistics::getMedianValue($cr_list);

            Offer::find($offer_id)->update([
                'yesterday_epc' => $yesterday_epc,
                'yesterday_cr' => $yesterday_cr
            ]);
        }
    }

    public function calcWeekCoefficients($offer_id)
    {
        $week_stat = HourlyStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(offer_hosts) AS offer_hosts'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits'))
            ->where('datetime', '>=', $this->week_ago)
            ->where('offer_id', $offer_id)
            ->groupBy('publisher_id')
            ->get();


        if ($week_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];
            foreach ($week_stat AS $stat) {

                if ($stat['offer_hosts'] >= (int)config('env.week_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$stat['leads_payout'], (int)$stat['hits']);
                    $cr = Statistics::calculateCr((int)$stat['approved_count'], (int)$stat['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $week_epc = Statistics::getMedianValue($epc_list);
            $week_cr = Statistics::getMedianValue($cr_list);

            Offer::find($offer_id)->update([
                'week_epc' => $week_epc,
                'week_cr' => $week_cr
            ]);
        }
    }

    public function calcMonthCoefficients($offer_id)
    {
        $month_stat = HourlyStat::select(
            DB::raw('SUM(approved_count) AS approved_count'),
            DB::raw('SUM(offer_hosts) AS offer_hosts'),
            DB::raw('SUM(leads_payout) AS leads_payout'),
            DB::raw('SUM(hits) AS hits'))
            ->where('datetime', '>=', $this->month_ago)
            ->where('offer_id', $offer_id)
            ->groupBy('publisher_id')
            ->get();

        if ($month_stat->count() > 0) {

            $epc_list = [];
            $cr_list = [];
            foreach ($month_stat AS $stat) {

                if ($stat['offer_hosts'] >= (int)config('env.month_reliable_hosts', 0)) {

                    $epc = Statistics::calculateEpc((float)$stat['leads_payout'], (int)$stat['hits']);
                    $cr = Statistics::calculateCr((int)$stat['approved_count'], (int)$stat['hits']);

                    $epc_list[] = $epc;
                    $cr_list[] = $cr;
                }
            }

            $month_epc = Statistics::getMedianValue($epc_list);
            $month_cr = Statistics::getMedianValue($cr_list);

            Offer::find($offer_id)->update([
                'month_epc' => $month_epc,
                'month_cr' => $month_cr
            ]);
        }
    }
}
