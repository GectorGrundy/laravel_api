<?php
declare(strict_types=1);

namespace App\Http\Requests\Country;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
	public function rules()
	{
		return [
			'with' => 'array',
			'with.*' => 'in:offers_quantity',
			'only_my' => 'in:1'
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('countries.on_get_list_error');
	}
}
