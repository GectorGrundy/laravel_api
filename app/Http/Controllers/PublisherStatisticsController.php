<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Strategies\PublisherStatistics\DayPublisherStatistics;
use App\Strategies\PublisherStatistics\MonthPublisherStatistics;
use App\Strategies\PublisherStatistics\WeekPublisherStatistics;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\PublisherStatistic as R;

class PublisherStatisticsController extends Controller
{
    use Helpers;

    public function getList(R\GetListRequest $request)
    {
        $currency_id = (int)$request->input('currency_id');

        switch ($request->input('period')) {
            case 'day':
                $statics = (new DayPublisherStatistics())->get($currency_id);
                break;

            case 'week':
                $statics = (new WeekPublisherStatistics())->get($currency_id);
                break;

            case 'month':
                $statics = (new MonthPublisherStatistics())->get($currency_id);
                break;
            default:
                throw new \LogicException();
        }

        return ['response' => $statics, 'status_code' => 200];
    }
}
