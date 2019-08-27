<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\LandingCreated;
use App\Events\LandingEdited;
use App\Jobs\MoveStaticFile;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Landing as R;
use App\Models\Landing;
use Hashids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LandingController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /landing.create landing.create
     * @apiGroup Landing
     * @apiPermission admin
     * @apiParam {Number} offer_id
     * @apiParam {Number} target_id
     * @apiParam {Number} locale_id
     * @apiParam {String{..255}} title
     * @apiParam {Number=0,1} is_active
     * @apiParam {Number=0,1} is_private
     * @apiParam {Number=0,1} is_responsive
     * @apiParam {Number=0,1} is_mobile
     * @apiParam {Number=0,1} is_advertiser_viewable
     * @apiParam {Number=0,1} is_external
     * @apiParam {Number=0,1} is_back_action
     * @apiParam {Number=0,1} is_back_call
     * @apiParam {Number=0,1} is_vibrate_on_mobile
     * @apiParam {String{..255}} [thumb_path]
     * @apiParam {Number=0,1} [is_address_on_success] Required if: <code>is_external=0</code>
     * @apiParam {Number=0,1} [is_email_on_success] Required if: <code>is_external=0</code>
     * @apiParam {Number=0,1} [is_custom_success] Required if: <code>is_external=0</code>
     * @apiParam {String{..255}} [subdomain] Unique for landings and transits. Required if: <code>is_external=0</code>
     * @apiParam {String} [realpath] Path to landing files. Required if: <code>is_external=0</code>
     * @apiSampleRequest /landing.create
     */
    public function create(R\CreateRequest $request)
    {
        $has_thumb = $request->filled('thumb_path');
        $landing = Landing::create(array_merge($request->all(), [
            'type' => Landing::COD,
            'has_thumb' => $has_thumb,
        ]));

        if ($has_thumb) {
            $this->dispatch(new MoveStaticFile(
                $request->get('thumb_path'),
                public_path(substr($landing->getThumbPath(), 1))
            ));
        }

        event(new LandingCreated($landing, $landing->getOriginal(), $request->realpath, $request->url));

        $landing = Landing::with(['locale', 'publishers', 'domains', 'target.template'])
            ->find($landing['id']);

        return $this->response->accepted(null, [
            'message' => trans('landings.on_create_success'),
            'response' => $landing,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /landing.edit landing.edit
     * @apiGroup Landing
     * @apiPermission admin
     * @apiParam {Number} id
     * @apiParam {Number} offer_id
     * @apiParam {Number} target_id
     * @apiParam {Number} locale_id
     * @apiParam {String{..255}} title
     * @apiParam {Number=0,1} is_active
     * @apiParam {Number=0,1} is_private
     * @apiParam {Number=0,1} is_responsive
     * @apiParam {Number=0,1} is_mobile
     * @apiParam {Number=0,1} is_advertiser_viewable
     * @apiParam {Number=0,1} is_external
     * @apiParam {Number=0,1} is_back_action
     * @apiParam {Number=0,1} is_back_call
     * @apiParam {Number=0,1} is_vibrate_on_mobile
     * @apiParam {String{..255}} thumb_path
     * @apiParam {Number=0,1} [is_address_on_success] Required if: <code>is_external=0</code>
     * @apiParam {Number=0,1} [is_email_on_success] Required if: <code>is_external=0</code>
     * @apiParam {Number=0,1} [is_custom_success] Required if: <code>is_external=0</code>
     * @apiParam {String{..255}} [subdomain] Unique for landings and transits. Required if: <code>is_external=0</code>
     * @apiParam {String} [realpath] Path to landing files. Required if: <code>is_external=0</code>
     * @apiSampleRequest /landing.edit
     */
    public function edit(R\EditRequest $request)
    {
        $landing = Landing::find($request->id);
        $original = $landing->getOriginal();
        $has_thumb = $request->filled('thumb_path');

        $landing->update(array_merge($request->all(), [
            'has_thumb' => (int)($has_thumb || $original['has_thumb']),
        ]));

        if (!$request->get('is_private')) {
            $landing->publishers()->sync([]);
        }

        if ($has_thumb) {
            $this->dispatch(new MoveStaticFile(
                $request->get('thumb_path'),
                public_path(substr($landing->getThumbPath(), 1))
            ));
        }

        event(new LandingEdited($landing, $original, $request->realpath, $request->url));

        $landing->load(['locale', 'publishers', 'domains', 'target.template']);

        return $this->response->accepted(null, [
            'message' => trans('landings.on_edit_success'),
            'response' => $landing,
            'status_code' => 202
        ]);
    }

    public function delete(R\DeleteRequest $request)
    {
        $landing = Landing::find($request['landing_id']);

        $landing->remove();

        return $this->response->accepted(null, [
            'message' => trans('landings.on_delete_success'),
            'status_code' => 202
        ]);
    }

    public function getByHash(R\GetByHashRequest $request)
    {
        $landing_id = \Hashids::decode($request->input('landing_hash'))[0];

        $landing = Landing::with($request->get('with', []));

        if ((int)$request->get('with_flow_landing_domain') === 1) {
            $flow_id = Hashids::decode($request->get('flow_hash'))[0] ?? 0;
            $landing->withFlowLandingDomain($landing_id, $flow_id);
        }

        $landing = $landing->find($landing_id);

        return ['response' => $landing, 'status_code' => 200];
    }


    /**
     * @api {GET} /landing.getList landing.getList
     * @apiGroup Landing
     * @apiPermission admin
     * @apiPermission publisher
     *
     * @apiParam {String[]=offers,publishers,locale,target,domains} [with[]]
     * @apiParam {String} [search] String for search by title. To search by hash, search have to start with `hash:`
     * @apiParam {String[]} [hashes[]] Get landings by this hashes
     * @apiParam {String[]} [offer_hashes[]]
     * @apiParam {Number=0,1} [is_mobile]
     * @apiParam {String=hash} [key_by]
     *
     * @apiSampleRequest /landing.getList
     */
    public function getList(R\GetListRequest $request)
    {
        /**
         * @var Collection $landings
         */
        $landings = Landing::with($request->get('with', []))
            ->select('landings.*')
            ->whereOffer($request->get('offer_hashes', []))
            ->search($request->get('search'))
            ->mobile($request->get('is_mobile'))
            ->when($request->input('hashes'), function (Builder $builder) use ($request) {
                $builder->whereIn('hash', rejectEmpty($request->input('hashes', [])));
            })
            ->userHaveAccess()
            ->latest('id')
            ->get();

        switch ($request->get('key_by')) {
            case 'hash':
                $landings = $landings->keyBy('hash');
                break;
        }

        return ['response' => $landings, 'status_code' => 200];
    }

    /**
     * Открытие лендинга заданным паблишерам
     *
     * @param R\SyncPublishersRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function syncPublishers(R\SyncPublishersRequest $request)
    {
        $landing = Landing::find($request->get('id'));

        $clone_for_sync = clone $landing;
        $clone_for_sync->publishers()->sync($request->get('publishers', []));

        $publishers = $landing->load(['publishers'])['publishers'];

        return $this->response->accepted(null, [
            'message' => trans('landings.on_sync_publishers_success'),
            'response' => $publishers,
            'status_code' => 202
        ]);
    }

    public function forceDelete(R\ForceDeleteRequest $request)
    {
        $landing = Landing::where('id', $request->get('landing_id'))->withTrashed()->first();

        //hard delete
        $landing->forceDelete();

        return $this->response->accepted(null, [
            'message' => trans('landings.on_force_delete_success'),
            'status_code' => 202
        ]);
    }

    public function restore(R\RestoreRequest $request)
    {
        $landing = Landing::where('id', $request->get('landing_id'))->onlyTrashed()->first();

        //restore
        $landing->restore();

        return $this->response->accepted(null, [
            'message' => trans('landings.on_restore_success'),
            'status_code' => 202
        ]);
    }
}
