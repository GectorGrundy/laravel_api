<?php
declare(strict_types=1);

namespace App\Strategies\PublisherStatistics;

use DB;
use Auth;
use Carbon\Carbon;
use App\Models\PublisherStatistic;
use Illuminate\Database\Eloquent\Collection;

class DayPublisherStatistics
{
    public function get(int $currency_id): Collection
    {
        $today = Carbon::now();
        $yesterday = clone $today;
        $yesterday->subDay();

        [$app_tz, $user_tz] = (new PublisherStatistic)->getTzs();

        return PublisherStatistic::select(
            'currency_id',
            DB::raw('SUM(`hosts`) as `hosts`'),
            DB::raw('SUM(`payout`) as `payout`'),
            DB::raw('SUM(`leads`) as `leads`'),
            DB::raw('SUM(`approved_leads`) as `approved_leads`'),
            DB::raw("HOUR(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}')) as `hour`")
        )
            ->currency($currency_id)
            ->publisher(Auth::user())
            ->createdBetween($yesterday, $today)
            ->groupBy(DB::raw("HOUR(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}'))"))
            ->get();
    }
}