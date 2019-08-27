<?php
declare(strict_types=1);

namespace App\Http\Requests\Landing;

use App\Http\Requests\Request;

class GetByHashRequest extends Request
{
	public function rules(): array
    {
		return [
			'landing_hash' => 'required|exists:landings,hash,deleted_at,NULL',
			'with.*' => 'in:offers,publishers,domains,locale',
			'with_flow_landing_domain' => 'in:0,1',
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('landings.on_get_error');
	}
}
