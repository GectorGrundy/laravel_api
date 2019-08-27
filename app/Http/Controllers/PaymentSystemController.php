<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Http\Requests\PaymentSystem as R;
use App\Models\PaymentSystem;

class PaymentSystemController extends Controller
{
	use Helpers;

	public function edit(R\EditRequest $request)
	{
		$payment_system = PaymentSystem::find($request->input('id'));

		$payment_system->update($request->all());

		return $this->response->accepted(null, [
			'message' => trans('payment_systems.on_edit_success'),
			'response' => $payment_system->load('publishers'),
			'status_code' => 202
		]);
	}

	public function getList(R\GetListRequest $request)
	{
		$payment_systems = PaymentSystem::with($request->input('with', []))->get();

		return ['response' => $payment_systems, 'status_code' => 200];
	}

    /**
     * Привязка категории к офферу
     *
     * @param R\SyncPublishersRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function syncPublishers(R\SyncPublishersRequest $request)
    {
        $payment_system = PaymentSystem::find($request->get('id'));

        $payment_system->publishers()->sync($request->get('publishers', []));

        return $this->response->accepted(null, [
            'message' => trans('payment_systems.on_sync_publishers_success'),
            'response' => $payment_system->load('publishers'),
            'status_code' => 202
        ]);
    }
}
