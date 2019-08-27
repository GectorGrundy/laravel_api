<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeo;

use App\Http\Requests\Request;

class GetListByHashRequest extends Request
{
	public function rules()
	{
		return [
			'target_hash' => 'required|exists:targets,hash',
			'with' => 'array',
			'with.*' => 'in:target,country,payout_currency,price_currency',
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('target_geo.on_get_list_error');
	}
}
