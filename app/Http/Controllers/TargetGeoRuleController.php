<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\TargetGeoRule\TargetGeoRuleCreated;
use App\Events\TargetGeoRule\TargetGeoRuleDeleted;
use App\Events\TargetGeoRule\TargetGeoRuleEdited;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\TargetGeoRule as R;
use App\Models\TargetGeoRule;

class TargetGeoRuleController extends Controller
{
    use Helpers;

    public function create(R\CreateRequest $request)
    {
        if ($request->get('is_fallback') == 1) {
            $this->makeAllRullesInTargetGeoAsIsNotFallback($request->get('target_geo_id'));
        }

        $target_geo_rule = TargetGeoRule::create($request->all())->load(['integration', 'advertiser']);

        event(new TargetGeoRuleCreated($target_geo_rule));

        return $this->response->accepted(null, [
            'message' => trans('target_geo_rules.on_create_success'),
            'response' => $target_geo_rule,
            'status_code' => 202
        ]);
    }

    public function edit(R\EditRequest $request)
    {
        if ($request->get('is_fallback') == 1) {
            $this->makeAllRullesInTargetGeoAsIsNotFallback($request->get('target_geo_id'));
        }

        TargetGeoRule::find($request->get('id'))->update($request->all());

        $target_geo_rule = TargetGeoRule::with(['integration', 'advertiser'])
            ->find($request->get('id'));

        event(new TargetGeoRuleEdited($target_geo_rule));

        return $this->response->accepted(null, [
            'message' => trans('target_geo_rules.on_edit_success'),
            'response' => $target_geo_rule,
            'status_code' => 202
        ]);
    }

    public function delete(R\DeleteRequest $request)
    {
        $target_geo_rule = TargetGeoRule::find($request->get('id'));
        $target_geo_rule->delete();

        event(new TargetGeoRuleDeleted($target_geo_rule));

        return $this->response->accepted(null, [
            'message' => trans('target_geo_rules.on_delete_success'),
            'status_code' => 202
        ]);
    }

    public function getList(R\GetListRequest $request)
    {
        $rules = TargetGeoRule::with($request->input('with', []))
            ->targetGeo($request->get('target_geo_id'))
            ->get();

        return [
            'response' => $rules,
            'status_code' => 200
        ];
    }

    /**
     * Обновление приоритета правил гео целей и возврат обновленной инфо по ним с нужными связями
     *
     * @param R\EditPriorityRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function editPriority(R\EditPriorityRequest $request)
    {
        foreach ($request->get('rules') AS $rule) {
            TargetGeoRule::find($rule['id'])->update(['priority' => $rule['priority']]);
        }

        return $this->response->accepted(null, [
            'message' => trans('target_geo_rules.on_edit_priority_success'),
            'status_code' => 202
        ]);
    }

    /**
     * Делаем все правила гео цели не по умолчанию
     *
     * @param $target_geo_id
     */
    private function makeAllRullesInTargetGeoAsIsNotFallback($target_geo_id)
    {
        TargetGeoRule::where('target_geo_id', $target_geo_id)->update(['is_fallback' => 0]);
    }
}
