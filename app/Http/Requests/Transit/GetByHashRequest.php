<?php
declare(strict_types=1);

namespace App\Http\Requests\Transit;

use App\Http\Requests\Request;

class GetByHashRequest extends Request
{
	public function rules(): array
    {
		return [
			'transit_hash' => 'required|exists:transits,hash,deleted_at,NULL',
			'with.*' => 'in:offers,publishers,domains,locale',
			'with_flow_transit_domain' => 'in:0,1',
		];
	}

	protected function getFailedValidationMessage(): string
	{
		return trans('transits.on_get_error');
	}
}
