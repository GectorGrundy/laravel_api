<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeo;

use App\Http\Requests\Request;

class GetListRequest extends Request
{

	public function rules()
	{
		return [
			'target_id' => 'required|exists:targets,id',
			'with' => 'array',
			'with.*' => 'in:target,country,payout_currency,price_currency',
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('target_geo.on_get_list_error');
	}
}
