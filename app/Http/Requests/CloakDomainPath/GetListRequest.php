<?php
declare(strict_types=1);

namespace App\Http\Requests\CloakDomainPath;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
	public function rules()
	{
		return [
		    'domain_hash' => 'required|string|exists:domains,hash,user_id,' . \Auth::id(),
			'with' => 'array',
			'with.*' => 'in:domain,flow,cloak'
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('cloak_domain_paths.on_get_list_error');
	}
}
