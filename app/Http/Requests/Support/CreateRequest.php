<?php
declare(strict_types=1);

namespace App\Http\Requests\Support;

use App\Http\Requests\Request;

class CreateRequest extends Request
{
	public function rules()
	{
		return [
			'email' => 'required|email|unique:users,email',
			'password' => 'required',
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('users.on_create_error');
	}
}
