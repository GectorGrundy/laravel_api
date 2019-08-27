<?php
declare(strict_types=1);

namespace App\Strategies\AdminDashboard;

use App\Models\HourlyStat;
use Carbon\Carbon;
use DB;

class MonthAdminDashboard
{
    public function get($currency_id)
    {
        $today = Carbon::now();
        $month_ago = clone $today;
        $today = $today->toDateTimeString();
        $month_ago = $month_ago->subMonth()->toDateTimeString();

        [$app_tz, $user_tz] = (new HourlyStat())->getTzs();

        $hourly_stats = HourlyStat::select(
            DB::raw('SUM(`flow_hosts`) as `hosts`'),
            DB::raw('SUM(`approved_count`) as `approved_leads`'),
            DB::raw('(SUM(`held_count`) + SUM(`approved_count`) + SUM(`cancelled_count`) + SUM(`trashed_count`))  as `leads`'),
            DB::raw("DATE(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}')) as `date`")
        )
            ->datetimeBetweenDatetimes($month_ago, $today)
            ->groupBy(DB::raw("DATE(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}'))"));

        if ($currency_id !== 'all') {
            $hourly_stats
                ->whereCurrencies([(int)$currency_id])
                ->addSelect(
                    DB::raw('SUM(`profit`) as `profit`'),
                    DB::raw('SUM(`leads_payout`) as `payout`')
                );
        }

        return $hourly_stats->get();
    }
}
