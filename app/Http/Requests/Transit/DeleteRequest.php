<?php
declare(strict_types=1);

namespace App\Http\Requests\Transit;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'hash' => 'required|exists:transits,hash,deleted_at,NULL'
		];
	}

	public function messages()
	{
		return [];
	}

	protected function getFailedValidationMessage()
	{
		return trans('transits.on_delete_error');
	}
}
