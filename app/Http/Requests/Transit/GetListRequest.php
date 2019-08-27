<?php
declare(strict_types=1);

namespace App\Http\Requests\Transit;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
	public function rules()
	{
		return [
			'offer_hashes'=> 'array',
			'offer_hashes.*' => 'exists:offers,hash',
			'key_by' => 'in:hash',
			'with.*' => 'in:offers,publishers,locale,target,domains',
			'is_mobile' => 'in:0,1',
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('landings.on_get_list_error');
	}
}
