<?php
declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class CreateAdministratorRequest extends Request
{
	public function rules()
	{
		return [
			'email' => 'required|email|unique:users,email',
			'password' => 'required|min:8',
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('users.on_create_error');
	}
}
