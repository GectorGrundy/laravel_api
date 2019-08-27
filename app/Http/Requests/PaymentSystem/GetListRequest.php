<?php
declare(strict_types=1);

namespace App\Http\Requests\PaymentSystem;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
	public function rules()
	{
		return [
			'with' => 'array',
			'with.*' => 'in:currency,publishers',
			'key_by' => 'in:id',
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('payment_systems.on_get_list_error');
	}
}
