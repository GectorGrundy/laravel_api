<?php
declare(strict_types=1);

namespace App\Http\Requests\Domain;

use App\Http\Requests\Request;

class GetByHashRequest extends Request
{
	public function rules(): array
    {
		return [
			'hash' => 'required|string',
            'with' => 'array',
            'with.*' => 'in:paths'
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('domains.on_get_error');
	}
}
