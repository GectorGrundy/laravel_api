<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\TargetGeo\TargetGeoCreated;
use App\Events\TargetGeo\TargetGeoEdited;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\TargetGeo as R;
use App\Models\{
    Target, TargetGeo
};
use Auth;
use Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @todo: Нужно переписывать метод, убрав передачу созданной сущности и ее связей
 */
class TargetGeoController extends Controller
{
    use Helpers;

    public function create(R\CreateRequest $request)
    {
        $is_default = $request->input('is_default');
        $target = Target::find($request->input('target_id'));

        if ($target->hasDefaultTargetGeo()) {
            if ($is_default) {
                $target->makeTargetGeoAsIsNotDefault();
            }
        } else {
            $is_default = 1;
        }

        $target_geo = TargetGeo::create(array_merge($request->all(), [
            'is_default' => $is_default,
        ]));

        event(new TargetGeoCreated($target_geo));

        return $this->response->accepted(null, [
            'message' => trans('target_geo.on_create_success'),
            'response' => $this->getByIdWithRelations($target_geo['id']),
            'status_code' => 202
        ]);
    }

    public function edit(R\EditRequest $request)
    {
        $target_geo = TargetGeo::find($request->input('id'));
        $target = Target::find($request->input('target_id'));
        $is_default = $request->input('is_default');

        if ($is_default) {
            $target->makeTargetGeoAsIsNotDefault((int)$target_geo['id']);
        } else {
            if (!$target->hasDefaultTargetGeo((int)$target_geo['id'])) {
                $is_default = 1;
            }
        }

        $target_geo->update(array_merge($request->only([
            'country_id', 'price_currency_id', 'price', 'old_price', 'hold_time', 'is_default', 'is_active',
            'target_geo_rule_sort_type'
        ]), [
            'is_default' => $is_default,
        ]));

        event(new TargetGeoEdited($target_geo));

        return $this->response->accepted(null, [
            'message' => trans('target_geo.on_edit_success'),
            'response' => $this->getByIdWithRelations($request->get('id')),
            'status_code' => 202
        ]);
    }

    public function delete(R\DeleteRequest $request)
    {
        $target_geo = TargetGeo::find($request->get('id'));

        $target_geo->remove();

        return $this->response->accepted(null, [
            'message' => trans('target_geo.on_delete_success'),
            'status_code' => 202
        ]);
    }

    public function getList(R\GetListRequest $request, TargetGeo $target_geo)
    {
        $publisher_id = 0;
        if (Auth::user()->isPublisher()) {
            $publisher_id = Auth::id();
        }

        $target_geo_list = $target_geo->getListByTargetId(
            (int)$request->get('target_id'),
            $request->get('with', []),
            $publisher_id
        );

        return ['response' => $target_geo_list, 'status_code' => 200];
    }

    public function getListByHash(R\GetListByHashRequest $request, TargetGeo $target_geo)
    {
        $target_id = Hashids::decode($request->get('target_hash'))[0];

        $target_geo_list = $target_geo->getListByTargetId($target_id, $request->get('with', []), Auth::user()->id);

        return ['response' => $target_geo_list, 'status_code' => 200];
    }

    /**
     * @api {GET} /target_geo.getById target_geo.getById
     * @apiGroup TargetGeo
     * @apiPermission admin
     * @apiParam {Number} id
     * @apiSampleRequest /target_geo.getById
     */
    public function getById(R\GetByIdRequest $request)
    {
        try {
            $target_geo = TargetGeo::with($request->input('with', []))
                ->findOrFail((int)$request->input('id'));

        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(trans('target_geo.on_get_error'));
            return;
        }

        return ['response' => $target_geo, 'status_code' => 200];
    }

    public function clone(R\CloneRequest $request)
    {
        $target_geo = TargetGeo::find($request->get('id'));

        $target_geo_for_clone = collect($target_geo)->except(['id', 'hash', 'is_default', 'is_active'])->toArray();

        $cloned_target_geo = TargetGeo::create(array_merge($target_geo_for_clone, $request->all()));

        if ($request->get('clone_rules')) {
            foreach ($target_geo->target_geo_rules as $rule) {
                $new_rule = $rule->replicate();
                $cloned_target_geo->target_geo_rules()->saveMany([$new_rule]);
                $new_rule->save();
            }
        }

        return $this->response->accepted(null, [
            'message' => trans('target_geo.on_clone_success'),
            'response' => $this->getByIdWithRelations($cloned_target_geo->id),
            'status_code' => 202
        ]);
    }

    private function getByIdWithRelations(int $target_geo_id): array
    {
        return TargetGeo::with([
            'country', 'payout_currency', 'price_currency', 'fallback_target_geo_rule',
            'target_geo_rules' => function ($q) {
                $q->orderBy('priority', 'desc');
            },
            'target_geo_rules.integration',
            'target_geo_rules.advertiser',
            'integration'
        ])->find($target_geo_id)
            ->toArray();
    }
}
