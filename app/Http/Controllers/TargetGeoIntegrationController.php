<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Models\TargetGeoIntegration;
use App\Http\Requests\TargetGeoIntegration as R;

class TargetGeoIntegrationController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /target_geo_integrations.create target_geo_integrations.create
     * @apiGroup TargetGeoIntegration
     * @apiPermission admin
     *
     * @apiParam {Number} advertiser_id
     * @apiParam {Number} target_geo_id
     * @apiParam {Number} currency_id
     * @apiParam {Number} charge
     * @apiParam {String=api,redirect} integration_type
     *
     * @apiSampleRequest /target_geo_integrations.create
     */
    public function create(R\CreateRequest $request)
    {
        $integration = TargetGeoIntegration::create($request->all());
        $integration = $integration->fresh()->load(['advertiser']);

        return $this->response->accepted(null, [
            'message' => trans('target_geo_integrations.on_create_success'),
            'response' => $integration,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /target_geo_integrations.edit target_geo_integrations.edit
     * @apiGroup TargetGeoIntegration
     * @apiPermission admin
     *
     * @apiParam {Number} id
     * @apiParam {Number} advertiser_id
     * @apiParam {Number} currency_id
     * @apiParam {Number} charge
     * @apiParam {String=api,redirect} integration_type
     *
     * @apiSampleRequest /target_geo_integrations.edit
     */
    public function edit(R\EditRequest $request)
    {
        $integration = TargetGeoIntegration::find($request->get('id'));
        $integration->update($request->only(['advertiser_id', 'charge', 'currency_id', 'integration_type']));
        $integration = $integration->load(['advertiser']);

        return $this->response->accepted(null, [
            'message' => trans('target_geo_integrations.on_edit_success'),
            'response' => $integration,
            'status_code' => 202
        ]);
    }
}
