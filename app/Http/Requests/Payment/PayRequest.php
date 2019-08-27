<?php
declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Http\Requests\Request;
use App\Models\Payment;

class PayRequest extends Request
{
	public function rules()
	{
		return [
			'hash' => 'required|exists:payments,hash,status,' . Payment::ACCEPTED,
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('payments.on_pay_error');
	}
}
