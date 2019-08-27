<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TargetGeoRule;
use Dingo\Api\Routing\Helpers;
use App\Services\TargetGeoRuleLeadStatService;
use App\Http\Requests\TargetGeoRuleStat as R;

class TargetGeoRuleStatController extends Controller
{
    use Helpers;

    public function reset(R\ResetRequest $request, TargetGeoRuleLeadStatService $stat_service)
    {
        $rule_ids = TargetGeoRule::where('target_geo_id', $request->target_geo_id)->get()->pluck('id')->toArray();

        $stat_service->reset($rule_ids, date('Y-m-d', time()));

        return $this->response->accepted(null, [
            'message' => trans('target_geo_rules.on_reset_stat_success'),
            'status_code' => 202
        ]);
    }
}
