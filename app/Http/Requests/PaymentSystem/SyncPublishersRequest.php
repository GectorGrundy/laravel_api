<?php
declare(strict_types=1);

namespace App\Http\Requests\PaymentSystem;

use App\Http\Requests\Request;
use App\Models\User;

class SyncPublishersRequest extends Request
{
	public function rules()
	{
		return [
			'id' => 'required|exists:payment_systems,id',
			'publishers' => 'array',
			'publishers.*' => 'numeric|exists:users,id,role,' . User::PUBLISHER,
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('payment_systems.on_sync_publishers_error');
	}
}
