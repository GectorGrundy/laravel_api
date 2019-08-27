<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TargetGeo;
use App\Models\UserGroupTargetGeo;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\UserGroupTargetGeo as R;

class UserGroupTargetGeoController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /user_group_target_geo.sync user_group_target_geo.sync
     * @apiGroup UserGroupTargetGeo
     * @apiPermission admin
     * @apiParam {Number} target_geo_id
     * @apiParam {Array} stakes Must have only one item with `is_default=1`
     * @apiParam target_geo_id
     * @apiParamExample {json} Request-Example:
     * { "target_geo_id": 1, "stakes": [
     *  {"user_group_id": 1, "payout": 18.00, "currency_id": 3, "is_default": 0}
     * ]}
     */
    public function sync(R\SyncRequest $request)
    {
        $stakes = (array)$request->input('stakes', []);
        $target_geo_id = (int)$request->input('target_geo_id', []);

        // Remove all custom stakes
        UserGroupTargetGeo::whereTargetGeo([$target_geo_id])->delete();

        foreach ($stakes as $stake) {

            if ($stake['is_default']) {
                TargetGeo::find($target_geo_id)->update([
                    'payout' => $stake['payout'],
                    'payout_currency_id' => $stake['currency_id'],
                ]);
            } else {
                UserGroupTargetGeo::whereGroup($stake['user_group_id'])
                    ->whereTargetGeo([$target_geo_id])
                    ->updateOrCreate([
                        'user_group_id' => $stake['user_group_id'],
                        'target_geo_id' => $target_geo_id,
                        'payout' => $stake['payout'],
                        'currency_id' => $stake['currency_id'],
                    ]);
            }
        }

        return $this->response->accepted(null, [
            'message' => trans('deposits.on_create_success'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /user_group_target_geo.getList user_group_target_geo.getList
     * @apiGroup UserGroupTargetGeo
     * @apiPermission admin
     * @apiParam {Number} target_geo_id
     * @apiSampleRequest /user_group_target_geo.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $list = UserGroupTargetGeo::whereTargetGeo([$request->get('target_geo_id')])->get();

        return ['response' => $list, 'status_code' => 200];
    }
}
