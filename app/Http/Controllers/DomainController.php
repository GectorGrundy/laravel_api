<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Hashids;
use App\Events\DomainCreated;
use App\Events\DomainEdited;
use App\Models\Flow;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Domain as R;
use App\Models\Domain;
use App\Services\Cloaking\Parser;

class DomainController extends Controller
{
    use Helpers;

    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @api {POST} /domain.create domain.create
     * @apiGroup Domain
     * @apiPermission admin
     * @apiPermission publisher
     *
     * @apiParam {String} domain
     * @apiParam {String} type
     * Admin allowed values: <code>custom,system</code><br>
     * Publisher allowed values: <code>parked</code>
     *
     * @apiParam {String} entity_type
     * Admin allowed values: <code>tds,redirect,landing,transit</code><br>
     * Publisher allowed values: <code>flow</code>
     *
     * @apiParam {String} [donor_url] For parked domains that uses new cloaking
     * @apiParam {Number=0,1} [is_public=1]
     * For parked domains it shows that domain can use for all flows
     * @apiParam {String} [fallback_flow_hash] Required if donor_url is empty
     * @apiParam {String} [entity_hash] Required for custom type. Landing or transit hash.
     * @apiParam {Number=0,1} [is_active=1] Only for system type
     *
     * @apiSampleRequest /domain.create
     */
    public function create(R\CreateRequest $request)
    {
        $domain = Domain::create(array_merge($request->all(), [
            'is_public' => $request->get('is_public', 1)
        ]));

        event(new DomainCreated($domain));

        if (\Auth::user()->isPublisher()) {
            $domain->load('flow', 'paths', 'replacements');
        }

        return [
            'message' => trans('domains.on_create_success'),
            'response' => $domain,
            'status_code' => 202
        ];
    }

    /**
     * @api {POST} /domain.edit deposit.edit
     * @apiGroup Domain
     * @apiPermission admin
     * @apiPermission publisher
     *
     * @apiParam {String} domain
     * @apiParam {String} domain_hash
     *
     * @apiParam {String} type
     * Admin allowed values: <code>custom,system</code><br>
     * Publisher allowed values: <code>parked</code>
     *
     * @apiParam {String} entity_type
     * Admin allowed values: <code>tds,redirect,landing,transit</code><br>
     * Publisher allowed values: <code>flow</code>
     *
     * @apiParam {String} [donor_url] For parked domains that uses new cloaking
     * @apiParam {Number=0,1} [is_public] 1 = Available for all flows
     * @apiParam {String} [fallback_flow_hash] Required if donor_url is empty
     * @apiParam {String} [entity_hash] Required for custom type. Landing or transit hash.
     *
     * @apiSampleRequest /deposit.edit
     */
    public function edit(R\EditRequest $request)
    {
        $domain = Domain::find($request->all()['domain_id']);

        $domain->update($request->all());

        event(new DomainEdited($domain));

        if (\Auth::user()->isPublisher()) {
            $domain->load('flow', 'paths', 'replacements');
        }

        return $this->response->accepted(null, [
            'message' => trans('domains.on_edit_success'),
            'response' => $domain,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /domain.delete deposit.delete
     * @apiGroup Domain
     * @apiPermission admin
     * @apiPermission publisher
     * @apiParam {String} domain_hash
     * @apiSampleRequest /deposit.delete
     */
    public function delete(R\DeleteRequest $request)
    {
        $domain_id = Hashids::decode($request->input('domain_hash'))[0];
        $domain = Domain::find($domain_id);

        $this->parser->deleteCacheDir($domain['host']);
        $domain->delete();

        return [
            'message' => trans('domains.on_delete_success'),
            'status_code' => 202
        ];
    }

    /**
     * @api {GET} /domain.getByHash domain.getByHash
     * @apiGroup Domain
     * @apiPermission publisher
     * @apiParam {String} hash
     * @apiParam {String[]=paths} [with[]]
     * @apiSampleRequest /domain.getByHash
     */
    public function getByHash(R\GetByHashRequest $request)
    {
        $domain = Domain::with($request->input('with', []))
            ->whereHash($request->input('hash'))
            ->first();

        return [
            'response' => $domain ?? [],
            'status_code' => 200
        ];
    }

    /**
     * @api {GET} /domain.getList domain.getList
     * @apiGroup Domain
     * @apiPermission admin
     * @apiPermission publisher
     * @apiParam {String} [flow_hash] Get domains assigned to this flow.
     * @apiParam {Number=0,1} [with_public] If set `with_public=0` - returns only domains for specified `flow_hash`
     * @apiParam {String[]=entity,entity.offer,entity.locale,flow,paths,replacements} [with[]]
     * @apiParam {String[]=tds,landing,transit,flow,redirect} [entity_types[]]
     * @apiSampleRequest /domain.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $domains = Domain::with($request->get('with', []));

        $domains->entityTypes($request->get('entity_types', []));

        if ($request->filled('flow_hash')) {
            $flow_id = (new Flow())->getIdFromHash($request->input('flow_hash'));

            $sql = " CASE WHEN entity_type = 'flow' THEN fallback_flow_id={$flow_id}";

            if ($request->input('with_public')) {
                $sql .= ' OR is_public=1';
            }

            $sql .= ' ELSE TRUE END';

            $domains->whereRaw($sql)
                ->orderByRaw("FIELD(fallback_flow_id, '{$flow_id}') DESC");

            if ($request->input('with_public')) {
                $domains->orderBy('is_public', 'DESC');
            }
        }

        $domains = $domains->latest('id')->get();

        return ['response' => $domains, 'status_code' => 200];
    }

    /**
     * @api {POST} /domain.clearCache domain.clearCache
     * @apiGroup Domain
     * @apiPermission publisher
     * @apiParam {String} hash
     * @apiSampleRequest /domain.clearCache
     */
    public function clearCache(R\ClearCacheRequest $request)
    {
        $id = Hashids::decode($request->input('hash'))[0];
        $domain = Domain::find($id);

        $this->parser->deletePage($domain['host']);

        return $this->response->accepted(null, [
            'message' => trans('domains.on_clear_cache_success'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /domain.activate domain.activate
     * @apiGroup Domain
     * @apiPermission admin
     * @apiParam {String} id
     * @apiSampleRequest /domain.activate
     */
    public function activate(R\ActivateRequest $request)
    {
        $domain = Domain::find($request->get('id'));
        $domain->update(['is_active' => 1]);

        return $this->response->accepted(null, [
            'message' => trans('domains.on_activate_success'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /domain.deactivate domain.deactivate
     * @apiGroup Domain
     * @apiPermission admin
     * @apiParam {String} id Id of not last redirect or tds domain.
     * @apiSampleRequest /domain.deactivate
     */
    public function deactivate(R\DeactivateRequest $request)
    {
        $domain = Domain::find($request->get('id'));
        $domain->update(['is_active' => 0]);

        return $this->response->accepted(null, [
            'message' => trans('domains.on_deactivate_success'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /domain.getRedirectDomain domain.getRedirectDomain
     * @apiGroup Domain
     * @apiPermission admin
     * @apiSampleRequest /domain.getRedirectDomain
     */
    public function getRedirectDomain()
    {
        $domain = Domain::entityTypes([Domain::REDIRECT_ENTITY_TYPE])->active()->latest('id')->first();

        return [
            'response' => $domain,
            'status_code' => 200
        ];
    }
}
