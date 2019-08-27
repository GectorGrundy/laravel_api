<?php
declare(strict_types=1);

namespace App\Http\Requests\Flow;

use App\Http\Requests\Request;

class GetLandingListRequest extends Request
{
	public function rules()
	{
		return [
			'flow_hash'=> 'required|exists:flows,hash',
			'with.*' => 'in:locale'
		];
	}
	
	protected function getFailedValidationMessage()
	{
		return trans('flows.on_get_list_error');
	}
}
