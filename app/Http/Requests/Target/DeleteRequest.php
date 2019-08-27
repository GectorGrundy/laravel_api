<?php
declare(strict_types=1);

namespace App\Http\Requests\Target;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
	public function rules(): array
    {
		return [
			'id' => 'required|exists:targets,id,deleted_at,NULL'
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('targets.on_delete_error');
	}
}
