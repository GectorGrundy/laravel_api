<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Strategies\AdminDashboard\DayAdminDashboard;
use App\Strategies\AdminDashboard\MonthAdminDashboard;
use App\Strategies\AdminDashboard\WeekAdminDashboard;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\AdminDashboard as R;

class AdminDashboardController extends Controller
{
    use Helpers;

    /**
     * @api {GET} /admin_dashboard.getList admin_dashboard.getList
     * @apiGroup AdminDashboard
     * @apiPermission admin
     *
     * @apiParam {String="day,week,month"} period
     * @apiParam {String=1,3,5,all} currency_id
     *
     * @apiSampleRequest /admin_dashboard.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $currency_id = $request->input('currency_id');

        switch ($request->input('period')) {
            case 'day':
                $statics = (new DayAdminDashboard())->get($currency_id);
                break;

            case 'week':
                $statics = (new WeekAdminDashboard())->get($currency_id);
                break;

            case 'month':
                $statics = (new MonthAdminDashboard())->get($currency_id);
                break;

            default:
                throw new \LogicException();
        }

        return ['response' => $statics, 'status_code' => 200];
    }
}
