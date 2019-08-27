<?php
declare(strict_types=1);

namespace App\Http\Requests\Domain;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
	public function rules(): array
    {
		return [
			'domain_hash' => 'required|exists:domains,hash,deleted_at,NULL'
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('domains.on_delete_error');
	}
}
