<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CcIntegrationWorkTime;
use App\Models\Integration;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Requests\Integration as R;

class IntegrationController extends Controller
{
	use Helpers;
	use DispatchesJobs;

    public function create(R\CreateRequest $request, CcIntegrationWorkTime $cc_integration_work_time): Response
    {
        $integration = Integration::create($request->all());

        $cc_integration_work_time->syncWorkdate($integration->id, $request->get('worktime'));

        return $this->response->accepted(null, [
            'message' => trans('integrations.on_create_success'),
            'response' => $integration->load(['worktime']),
            'status_code' => 202
        ]);
    }

	public function edit(R\EditRequest $request, CcIntegrationWorkTime $cc_integration_work_time): Response
    {
		Integration::find($request->get('id'))->update($request->all());

        $cc_integration_work_time->syncWorkdate($request->get('id'), $request->get('worktime'));

        $integration = Integration::with(['worktime'])->find($request->get('id'));

		return $this->response->accepted(null, [
			'message' => trans('integrations.on_edit_success'),
			'response' => $integration,
			'status_code' => 202
		]);
	}

	public function delete(R\DeleteRequest $request): Response
    {
		Integration::find($request->get('id'))->delete();

		return $this->response->accepted(null, [
			'message' => trans('integrations.on_delete_success'),
			'status_code' => 202
		]);
	}

	public function getById(R\GetByIdRequest $request): array
    {
		$integration = Integration::find($request->get('id'));

		return ['response' => $integration, 'status_code' => 200];
	}

	public function getList(): array
    {
		$integrations = Integration::with(['worktime'])->latest('id')->get();

		return ['response' => $integrations, 'status_code' => 200];
	}
}
