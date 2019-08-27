<?php
declare(strict_types=1);

namespace App\Http\Requests\Statistic;

use App\Http\Requests\Request;

class GetOrderInfoRequest extends Request
{
	public function rules()
	{
		return [
			'id' => 'required|numeric',
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('statistics.on_get_order_info_error');
	}
}
