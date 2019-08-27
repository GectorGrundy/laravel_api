<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Offer;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Target as R;
use App\Models\Target;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TargetController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /target.create target.create
     * @apiGroup Target
     * @apiPermission admin
     * @apiParam {String{..255}} label
     * @apiParam {String=CPA,CPL} type
     * @apiParam {Number} target_template_id
     * @apiParam {Number} offer_id
     * @apiParam {Number} locale_id
     * @apiParam {Number=0,1} is_active
     * @apiParam {Number=0,1} is_default
     * @apiParam {Number=0,1} is_private
     * @apiParam {Number=0,1} is_autoapprove=0  May be set to `1` only if `type=CPL`
     * @apiParam {String=external,internal} landing_type
     * @apiSampleRequest /target.create
     */
    public function create(R\CreateRequest $request)
    {
        $offer = Offer::find($request->get('offer_id'));
        $is_default = $request->input('is_default');
        $is_autoapprove = $request->type === Target::CPL ? $request->input('is_autoapprove', 0) : 0;

        if ($offer->hasDefaultTarget()) {
            if ($is_default) {
                $offer->makeTargetAsIsNotDefault();
            }
        } else {
            $is_default = 1;
        }


        $target = Target::create(array_merge($request->all(), [
            'is_default' => $is_default,
            'is_autoapprove' => $is_autoapprove,
        ]));

        $target = Target::with([
            'template',
            'target_geo', 'target_geo.country', 'target_geo.payout_currency', 'target_geo.price_currency',
            'target_geo.fallback_target_geo_rule',
            'target_geo.target_geo_rules' => function (HasMany $builder) {
                $builder->latest('priority');
            },
            'target_geo.target_geo_rules.integration',
            'target_geo.target_geo_rules.advertiser'
        ])
            ->find($target['id']);

        return $this->response->accepted(null, [
            'message' => trans('targets.on_create_success'),
            'response' => $target,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /target.edit target.edit
     * @apiGroup Target
     * @apiPermission admin
     * @apiParam {Number} id
     * @apiParam {String{..255}} label
     * @apiParam {String=CPA,CPL} type
     * @apiParam {Number} target_template_id
     * @apiParam {Number} offer_id
     * @apiParam {Number} locale_id
     * @apiParam {Number=0,1} is_active
     * @apiParam {Number=0,1} is_default
     * @apiParam {Number=0,1} is_private
     * @apiParam {Number=0,1} is_autoapprove=0  May be set to `1` only if `type=CPL`
     * @apiSampleRequest /target.edit
     */
    public function edit(R\EditRequest $request)
    {
        $target = Target::with(['offer'])->find($request->get('id'));
        $is_autoapprove = $request->type === Target::CPL ? $request->input('is_autoapprove', 0) : 0;

        $is_default = $request->input('is_default');
        if ($is_default) {
            $target->offer->makeTargetAsIsNotDefault((int)$target['id']);
        } else {
            if (!$target->offer->hasDefaultTarget((int)$target['id'])) {
                $is_default = 1;
            }
        }
        $target->update(array_merge($request->except('landing_type'), [
            'is_default' => $is_default,
            'is_autoapprove' => $is_autoapprove,
        ]));

        $target = Target::with([
            'template',
            'target_geo', 'target_geo.country', 'target_geo.payout_currency', 'target_geo.price_currency',
            'target_geo.fallback_target_geo_rule',
            'target_geo.target_geo_rules' => function (HasMany $builder) {
                $builder->latest('priority');
            },
            'target_geo.target_geo_rules.integration',
            'target_geo.target_geo_rules.advertiser'
        ])->find($request->get('id'));


        return $this->response->accepted(null, [
            'message' => trans('targets.on_edit_success'),
            'response' => $target,
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /target.delete target.delete
     * @apiGroup Target
     * @apiPermission admin
     * @apiParam {Number} id
     * @apiSampleRequest /target.delete
     */
    public function delete(R\DeleteRequest $request)
    {
        $target = Target::with(['landings', 'transits', 'target_geo'])->find($request->get('id'));

        $target->landings->each->remove();
        $target->transits->each->remove();
        $target->target_geo->each->remove();

        $target->delete();

        return $this->response->accepted(null, [
            'message' => trans('targets.on_delete_success'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /target.getList target.getList
     * @apiGroup Target
     * @apiPermission admin
     * @apiPermission publisher
     * @apiParam {String[]} [offer_hashes[]]
     * @apiSampleRequest /target.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $req_with = $request->get('with', []);
        $with = [];
        if (in_array('landings.locale', $req_with) || in_array('landings.domain', $req_with)) {
            $with['landings'] = function (HasMany $query) {
                return $query->userHaveAccess();
            };
        }
        if (in_array('transits.locale', $req_with) || in_array('transits.domain', $req_with)) {
            $with['transits'] = function (HasMany $query) {
                return $query->userHaveAccess();
            };
        }
        $with = array_merge($with, $req_with);

        $targets = Target::with($with)
            ->whereOffer($request->get('offer_hashes', []))
            ->availableForUser(\Auth::user())
            ->get();

        return ['response' => $targets, 'status_code' => 200];
    }

    /**
     * @api {POST} /target.syncPublishers target.syncPublishers
     * @apiDescription Set permissions by user groups.
     * To forbid access for all publishers, do not send publishers[] param.
     * @apiGroup Target
     * @apiPermission admin
     * @apiParam {Number} target_id
     * @apiParam {Object[]} [publishers][]
     * @apiParamExample {json} Request-Example:
     * { "target_id": 1, "publishers": [
     *  {"publisher_id": 1}, {"publisher_id": 2}
     * ]}
     */
    public function syncPublishers(R\SyncPublishersRequest $request)
    {
        $target = Target::find($request->get('target_id'));

        $clone_for_sync = clone $target;
        $clone_for_sync->publishers()->sync($request->get('publishers', []));

        $publishers = $target->load(['publishers'])['publishers'];

        return $this->response->accepted(null, [
            'message' => trans('targets.on_change_privacy_success'),
            'response' => $publishers,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /target.syncUserGroups target.syncUserGroups
     * @apiDescription Set permissions by user groups.
     * To forbid access for all groups, do not send user_groups[] param.
     * @apiGroup Target
     * @apiPermission admin
     * @apiParam {Number} target_id
     * @apiParam {Object[]} [user_groups][]
     * @apiParamExample {json} Request-Example:
     * { "target_id": 1, "user_groups": [
     *  {"user_group_id": 1}, {"user_group_id": 2}
     * ]}
     */
    public function syncUserGroups(R\SyncUserGroupsRequest $request)
    {
        $target = Target::find($request->get('target_id'));

        $target->user_groups()->sync(
            collect($request->get('user_groups', []))->keyBy('user_group_id')->toArray()
        );

        $user_groups = $target->load(['user_groups'])['user_groups'];

        return $this->response->accepted(null, [
            'message' => trans('targets.on_change_privacy_success'),
            'response' => $user_groups,
            'status_code' => 202
        ]);
    }
}
