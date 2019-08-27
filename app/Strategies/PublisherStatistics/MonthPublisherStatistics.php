<?php
declare(strict_types=1);

namespace App\Strategies\PublisherStatistics;

use DB;
use Auth;
use Carbon\Carbon;
use App\Models\PublisherStatistic;
use Illuminate\Database\Eloquent\Collection;

class MonthPublisherStatistics
{
    public function get(int $currency_id): Collection
    {
        $today = Carbon::now();
        $month_ago = clone $today;
        $month_ago->subMonth();

        [$app_tz, $user_tz] = (new PublisherStatistic)->getTzs();

        return PublisherStatistic::select(
            'currency_id',
            DB::raw('SUM(`hosts`) as `hosts`'),
            DB::raw('SUM(`payout`) as `payout`'),
            DB::raw('SUM(`leads`) as `leads`'),
            DB::raw('SUM(`approved_leads`) as `approved_leads`'),
            DB::raw("DATE(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}')) as `date`")
        )
            ->currency($currency_id)
            ->publisher(Auth::user())
            ->createdBetween($month_ago, $today)
            ->groupBy(DB::raw("DATE(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}'))"))
            ->get();
    }
}