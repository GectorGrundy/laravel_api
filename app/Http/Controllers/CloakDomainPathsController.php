<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CloakDomainPath;
use App\Models\CloakDomainPathCloakSystem;
use Auth;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\CloakDomainPath as R;
use Hashids;

class CloakDomainPathsController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /cloak_domain_paths.create cloak_domain_paths.create
     * @apiGroup CloakDomainPath
     * @apiPermission publisher
     *
     * @apiUse cloak_domain_path_status
     * @apiUse cloak_domain_path_data_parameter
     * @apiParam {String} flow_hash
     * @apiParam {String} domain_hash
     * @apiParam {String} path Unique path per domain.
     * @apiParam {Number} cloak_system_id
     * @apiParam {Number=0,1} is_cache_result
     * @apiParam {String} [identifiers]  Campaign ids, each with a new line.
     * <br>Required if <code>status=moneypage_for</code>
     *
     * @apiSampleRequest /cloak_domain_paths.create
     */
    public function create(R\CreateRequest $request)
    {
        $flow_id = Hashids::decode($request->input('flow_hash'))[0];
        $domain_id = Hashids::decode($request->input('domain_hash'))[0];

        $path = CloakDomainPath::create(array_merge($request->all(), [
            'user_id' => Auth::id(),
            'flow_id' => $flow_id,
            'domain_id' => $domain_id,
        ]));

        CloakDomainPathCloakSystem::create([
            'cloak_domain_path_id' => $path->id,
            'cloak_system_id' => $request->input('cloak_system_id'),
            'is_cache_result' => $request->input('is_cache_result'),
            'attributes' => json_encode($request->get('attributes', []))
        ]);

        return $this->response->accepted(null, [
            'message' => trans('cloak_domain_paths.on_create_success'),
            'response' => $path->load('flow', 'cloak'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /cloak_domain_paths.edit cloak_domain_paths.edit
     * @apiGroup CloakDomainPath
     * @apiPermission publisher
     *
     * @apiUse cloak_domain_path_status
     * @apiUse cloak_domain_path_data_parameter
     * @apiParam {String} hash
     * @apiParam {String} flow_hash
     * @apiParam {String} domain_hash
     * @apiParam {String} path Unique path per domain.
     * @apiParam {Number} cloak_system_id
     * @apiParam {Number=0,1} is_cache_result
     * @apiParam {String} [identifiers]  Campaign ids, each with a new line.
     * <br>Required if <code>status=moneypage_for</code>
     *
     * @apiSampleRequest /cloak_domain_paths.edit
     */
    public function edit(R\EditRequest $request)
    {
        $id = Hashids::decode($request->input('hash'))[0];
        $flow_id = Hashids::decode($request->input('flow_hash'))[0];

        $path = CloakDomainPath::find($id);

        $path->update(array_merge($request->except('domain_id'), [
            'flow_id' => $flow_id,
        ]));

        // Update cloaking settings
        $cloaking = CloakDomainPathCloakSystem::where('cloak_domain_path_id', $path->id)->first();
        $cloaking->update([
            'cloak_system_id' => $request->input('cloak_system_id'),
            'is_cache_result' => $request->input('is_cache_result'),
            'attributes' => json_encode($request->get('attributes', []))
        ]);

        return $this->response->accepted(null, [
            'message' => trans('cloak_domain_paths.on_edit_success'),
            'response' => $path->load('flow', 'cloak'),
            'status_code' => 202
        ]);
    }

    public function delete(R\DeleteRequest $request)
    {
        $id = Hashids::decode($request->input('hash'))[0];
        $path = CloakDomainPath::find($id);

        $path->delete();

        return $this->response->accepted(null, [
            'message' => trans('cloak_domain_paths.on_delete_success'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /cloak_domain_paths.getList cloak_domain_paths.getList
     * @apiGroup CloakDomainPath
     * @apiPermission publisher
     *
     * @apiParam {String} domain_hash
     * @apiParam {String[]=domain,flow,cloak} [with[]]
     * @apiSampleRequest /cloak_domain_paths.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $paths = CloakDomainPath::with($request->get('with', []))
            ->whereDomainHash($request->input('domain_hash'))
            ->latest('id')
            ->get();

        return ['response' => $paths, 'status_code' => 200];
    }
}
