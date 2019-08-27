<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Hashids;
use App\Models\Transit;
use App\Events\TransitCreated;
use App\Events\TransitEdited;
use App\Jobs\MoveStaticFile;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Transit as R;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TransitController extends Controller
{
    use Helpers;

    public function create(R\CreateRequest $request)
    {
        $transit = Transit::create($request->all());

        $this->dispatch(new MoveStaticFile(
            $request->get('thumb_path'),
            public_path(substr($transit->getThumbPath(), 1))
        ));

        event(new TransitCreated($transit, $transit->getOriginal(), $request->input('realpath')));

        $transit = Transit::with(['locale', 'publishers', 'domains', 'target.template'])
            ->find($transit['id']);

        return $this->response->accepted(null, [
            'message' => trans('transits.on_create_success'),
            'response' => $transit,
            'status_code' => 202
        ]);
    }

    public function edit(R\EditRequest $request)
    {
        $transit = Transit::find($request->get('id'));
        $original = $transit->getOriginal();

        $transit->update($request->all());

        if (!$request->get('is_private')) {
            $transit->publishers()->sync([]);
        }

        if ($request->filled('thumb_path')) {
            $this->dispatch(new MoveStaticFile(
                $request->get('thumb_path'),
                public_path(substr($transit->getThumbPath(), 1))
            ));
        }

        event(new TransitEdited($transit, $original, $request->input('realpath')));

        $transit = Transit::with(['locale', 'publishers', 'domains', 'target.template'])
            ->find($transit['id']);

        return $this->response->accepted(null, [
            'message' => trans('transits.on_edit_success'),
            'response' => $transit,
            'status_code' => 202
        ]);
    }

    public function delete(R\DeleteRequest $request)
    {
        $transit_id = Hashids::decode($request->get('hash'))[0];
        $transit = Transit::find($transit_id);

        $transit->remove();

        return $this->response->accepted(null, [
            'message' => trans('transits.on_delete_success'),
            'status_code' => 202
        ]);
    }

    public function getByHash(R\GetByHashRequest $request)
    {
        $transit_id = Hashids::decode($request->get('transit_hash'))[0];

        $transit = Transit::with($request->get('with', []));

        if ($request->get('with_flow_transit_domain') == 1) {

            $flow_id = Hashids::decode($request->get('flow_hash'))[0] ?? 0;
            $transit->withFlowTransitDomain($transit_id, $flow_id);
        }


        $transit = $transit->find($transit_id);

        return ['response' => $transit, 'status_code' => 200];
    }

    /**
     * @api {GET} /transit.getList transit.getList
     * @apiGroup Transit
     * @apiPermission admin
     * @apiPermission publisher
     *
     * @apiParam {String[]=offers,publishers,locale,target,domains} [with[]]
     * @apiParam {String} [search] String for search by title. To search by hash, search have to start with `hash:`
     * @apiParam {String[]} [hashes[]] Get transits by this hashes
     * @apiParam {String[]} [offer_hashes[]]
     * @apiParam {Number=0,1} [is_mobile]
     * @apiParam {String=hash} [key_by]
     *
     * @apiSampleRequest /transit.getList
     */
    public function getList(R\GetListRequest $request)
    {
        /**
         * @var Collection $transits
         */
        $transits = Transit::with($request->get('with', []))
            ->select('transits.*')
            ->whereOffer($request->get('offer_hashes', []))
            ->search($request->get('search'))
            ->when($request->input('hashes'), function (Builder $builder) use ($request) {
                $builder->whereIn('hash', rejectEmpty($request->input('hashes', [])));
            })
            ->userHaveAccess()
            ->mobile($request->get('is_mobile'))
            ->latest('id')
            ->get();

        switch ($request->get('key_by')) {
            case 'hash':
                $transits = $transits->keyBy('hash');
                break;
        }

        return ['response' => $transits, 'status_code' => 200];
    }

    public function syncPublishers(R\SyncPublishersRequest $request)
    {
        $transit = Transit::find($request->get('id'));

        $clone_for_sync = clone $transit;
        $clone_for_sync->publishers()->sync($request->get('publishers', []));

        $publishers = $transit->load(['publishers'])['publishers'];

        return $this->response->accepted(null, [
            'message' => trans('transits.on_sync_publishers_success'),
            'response' => $publishers,
            'status_code' => 202
        ]);
    }
}
