<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\MyOffer;
use App\Models\Scopes\HideDraftStatus;
use Auth;
use DB;
use Hashids;
use App\Events\Flow\FlowCreated;
use App\Events\Flow\FlowEdited;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Flow as R;
use App\Models\Flow;
use App\Models\Transit;
use App\Models\Landing;

class FlowController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /flow.create flow.create
     * @apiGroup Flow
     * @apiPermission publisher
     * @apiParam {String} offer_hash
     * @apiSampleRequest /flow.create
     */
    public function create(R\CreateRequest $request)
    {
        $offer_id = Hashids::decode($request->input('offer_hash'))[0];
        $flow = Flow::create([
            'publisher_id' => Auth::user()->id,
            'offer_id' => $offer_id,
            'is_hide_target_list' => 1,
        ]);

        event(new FlowCreated($flow));

        return $this->response->accepted(null, [
            'message' => trans('flows.on_create_success'),
            'response' => $flow,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /flow.createVirtual flow.createVirtual
     * @apiGroup Flow
     * @apiPermission publisher
     * @apiParam {String} offer_hash
     * @apiParam {String} landing_hash
     * @apiParam {String} transit_hash
     * @apiSampleRequest /flow.createVirtual
     */
    public function createVirtual(R\CreateVirtualRequest $request)
    {
        $offer_id = (int)Hashids::decode($request->input('offer_hash'))[0];
        $landing_id = (int)Hashids::decode($request->input('landing_hash'))[0];

        $landing = Landing::find($landing_id);

        $flow = Flow::create([
            'publisher_id' => Auth::id(),
            'offer_id' => $offer_id,
            'target_id' => $landing['target_id'],
            'status' => Flow::ACTIVE,
            'is_virtual' => 1,
        ]);

        $landings = [$request->get('landing_hash')];
        $this->syncLandings($flow['id'], $landings);

        $transits = $request->filled('transit_hash') ? [$request->input('transit_hash')] : [];
        $this->syncTransits($flow['id'], $transits);

        $landing_domain = Domain::getDefaultTds($landing_id);
        $fast_link = $landing_domain['host'] . '?flow_hash=' . $flow['hash'];

        MyOffer::createNew($offer_id, Auth::user()->id);

        return $this->response->accepted(null, [
            'message' => trans('flows.on_create_success'),
            'response' => [
                'fast_link' => $fast_link,
                'flow_hash' => $flow['hash'],
            ],
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /flow.editVirtual flow.editVirtual
     * @apiGroup Flow
     * @apiPermission publisher
     * @apiParam {String} flow_hash
     * @apiParam {String} landing_hash
     * @apiParam {String} transit_hash
     * @apiSampleRequest /flow.editVirtual
     */
    public function editVirtual(R\EditVirtualRequest $request)
    {
        $flow_id = \Hashids::decode($request->input('flow_hash'))[0];
        $flow = Flow::find($flow_id);

        $landing_id = \Hashids::decode($request->input('landing_hash'))[0];
        $landing = Landing::find($landing_id);

        $landings = [$request->get('landing_hash')];
        $this->syncLandings($flow['id'], $landings);

        $transits = $request->filled('transit_hash') ? [$request->input('transit_hash')] : [];
        $this->syncTransits($flow['id'], $transits);

        $flow->update(['target_id' => $landing['target_id']]);

        return $this->response->accepted(null, [
            'message' => trans('flows.on_edit_success'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /flow.edit flow.edit
     * @apiGroup Flow
     * @apiPermission publisher
     *
     * @apiParam {String} hash
     * @apiParam {String} target_hash
     * @apiParam {String} offer_hash
     * @apiParam {String} group_hash
     * @apiParam {String{..255}} title
     * @apiParam {String[]} landings Array of landing hashes
     * @apiParam {String[]} transits Array of transit hashes
     * @apiParam {Number=0,1} is_detect_bot
     * @apiParam {Number=0,1} is_hide_target_list
     * @apiParam {Number=0,1} is_noback
     * @apiParam {Number=0,1} is_show_requisite
     * @apiParam {Number=0,1} is_remember_landing
     * @apiParam {Number=0,1} is_remember_transit
     * @apiParam {String} extra_flow_hash
     * @apiParam {String} tb_url
     * @apiParam {Number{..100000}} [back_action_sec]
     * @apiParam {Number{..100000}} [back_call_btn_sec]
     * @apiParam {Number{..100000}} [back_call_form_sec]
     * @apiParam {Number{..100000}} [vibrate_on_mobile_sec]
     *
     * @apiSampleRequest /flow.edit
     */
    public function edit(R\EditRequest $request)
    {
        $flow_id = Hashids::decode($request->input('hash'))[0];
        $offer_id = Hashids::decode($request->input('offer_hash'))[0];

        $flow = Flow::withoutGlobalScope(HideDraftStatus::class)->find($flow_id);

        $message = trans('flows.on_edit_success');
        if ($flow['status'] === Flow::DRAFT) {
            $message = trans('flows.on_create_success');
        }

        $flow->update(array_merge($request->all(), [
            'title' => $request->filled('title') ? $request->input('title') : $flow->getDefaultTitle(),
            'status' => Flow::ACTIVE,
        ]));

        $this->syncLandings($flow_id, $request->get('landings', []));
        $this->syncTransits($flow_id, $request->get('transits', []));

        MyOffer::createNew($offer_id, Auth::user()['id']);

        event(new FlowEdited($flow));

        return $this->response->accepted(null, [
            'message' => $message,
            'response' => $flow,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /flow.delete flow.delete
     * @apiGroup Flow
     * @apiPermission publisher
     * @apiParam {String} flow_hash
     * @apiSampleRequest /flow.delete
     */
    public function delete(R\DeleteRequest $request)
    {
        $id = Hashids::decode($request->input('flow_hash'))[0];

        Flow::where('id', $id)->update([
            'status' => Flow::ARCHIVED,
        ]);

        return $this->response->accepted(null, [
            'message' => trans('flows.on_delete_success'),
            'status_code' => 202
        ]);
    }

    public function getById(R\GetByIdRequest $request)
    {
        $id = Hashids::decode($request->input('flow_hash'))[0];

        $flow = Flow::with($request->get('with'))->find($id);

        return ['response' => $flow, 'status_code' => 200];
    }

    public function getByHash(R\GetByHashRequest $request)
    {
        $flow = Flow::with($request->input('with', []));

        if ($request->input('with_draft', false)) {
            $flow->withoutGlobalScope(HideDraftStatus::class);
        }

        $flow = $flow->find($request->input('id'));

        return ['response' => $flow, 'status_code' => 200];
    }

    public function getList(R\GetListRequest $request)
    {
        $flows = Flow::with($request->input('with', []))
            ->withoutVirtual()
            ->active()
            ->whereCurrencies($request->input('currency_ids', []))
            ->search($request->input('search'))
            ->offerHashes($request->input('offer_hashes'))
            ->groupHashes($request->input('group_hashes'))
            ->latest('id')
            ->get()
            ->toArray();

        if (\in_array('day_statistics', $request->input('with', []))) {
            foreach ($flows as &$flow) {
                /**
                 * @var Flow $flow
                 */
                $flow['day_statistics'] = Flow::normalizeDayStatistics($flow);
            }
        }

        return $this->response->array(['response' => $flows, 'status_code' => 200]);
    }

    private function syncLandings($flow_id, array $landings)
    {
        DB::table('flow_landing')->where('flow_id', $flow_id)->delete();

        if (!\count($landings)) {
            return;
        }

        foreach ($landings AS $landing_hash) {
            DB::table('flow_landing')->insert([
                'flow_id' => $flow_id,
                'landing_id' => Hashids::decode($landing_hash)[0],
            ]);
        }
    }

    private function syncTransits($flow_id, array $transits)
    {
        DB::table('flow_transit')->where('flow_id', $flow_id)->delete();

        if (!\count($transits)) {
            return;
        }

        foreach ($transits AS $transit_hash) {
            DB::table('flow_transit')->insert([
                'flow_id' => $flow_id,
                'transit_id' => Hashids::decode($transit_hash)[0],
            ]);
        }
    }

    /**
     * Получение списка прелендингов потока
     *
     * @param R\GetTransitListRequest $request
     * @return array
     */
    public function getTransitList(R\GetTransitListRequest $request)
    {
        $flow_id = Hashids::decode($request->get('flow_hash'))[0];

        $transit_list = Transit::with($request->get('with', []))
            ->select('transits.*')
            ->leftJoin('flow_transit', 'flow_transit.transit_id', '=', 'transits.id')
            ->where('flow_transit.flow_id', $flow_id)
            ->get();

        return ['response' => $transit_list, 'status_code' => 200];

    }

    /**
     * Получение списка лендингов потока
     *
     * @param R\GetLandingListRequest $request
     * @return array
     */
    public function getLandingList(R\GetLandingListRequest $request)
    {
        $flow_id = Hashids::decode($request->get('flow_hash'))[0];

        $transit_list = Landing::with($request->get('with', []))
            ->select('landings.*')
            ->leftJoin('flow_landing', 'flow_landing.landing_id', '=', 'landings.id')
            ->where('flow_landing.flow_id', $flow_id)
            ->get();

        return ['response' => $transit_list, 'status_code' => 200];

    }

    public function clone(R\CloneRequest $request)
    {
        $flow_id = Hashids::decode($request->input('flow_hash'))[0];

        $flow = Flow::find($flow_id);

        $new_flow = $flow->replicate([
            'is_active', 'today_epc', 'yesterday_epc', 'week_epc', 'month_epc', 'today_cr', 'yesterday_cr', 'week_cr',
            'month_cr',
        ]);

        $new_flow->title = $request->input('title');
        $new_flow->save();

        // Копируем лендинги
        $flow_landings = DB::table('flow_landing')->where('flow_id', $flow_id)->get();
        if ($flow_landings !== null) {

            foreach ($flow_landings AS $flow_landing) {

                DB::table('flow_landing')->insert([
                    'flow_id' => $new_flow['id'],
                    'landing_id' => $flow_landing->landing_id,
                ]);
            }
        }

        // Копируем прелендинги
        $flow_transits = DB::table('flow_transit')->where('flow_id', $flow_id)->get();
        if ($flow_landings !== null) {

            foreach ($flow_transits AS $flow_transit) {

                DB::table('flow_transit')->insert([
                    'flow_id' => $new_flow['id'],
                    'transit_id' => $flow_transit->transit_id,
                ]);
            }
        }

        // Копируем постбеки если паблишер это разрешил и в потока были настроены постбеки
        if ($request->get('clone_postbacks') == 1 && $flow->postbacks->count() > 0) {
            foreach ($flow->postbacks AS $postback) {
                $new_postback = $postback->replicate();
                $new_flow->postbacks()->save($new_postback);
            }
        }

        return $this->response->accepted(null, [
            'message' => trans('flows.on_clone_success'),
            'response' => [
                'hash' => $new_flow['hash']
            ],
            'status_code' => 202
        ]);
    }

    public function getByTitle(R\GetByTitleRequest $request)
    {
        $flow = Flow::with($request->input('with', []))
            ->where('title', $request->input('title'))
            ->first();

        if (is_null($flow)) {
            return [
                'message' => trans('flows.on_get_error'),
                'status_code' => 404
            ];
        }

        return [
            'response' => $flow,
            'status_code' => 200
        ];
    }
}
