<?php
declare(strict_types=1);

namespace App\Http\Requests\SmsIntegration;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [];
	}

	public function messages()
	{
		return [];
	}

	protected function getFailedValidationMessage()
	{
		return trans('integrations.on_get_list_error');
	}
}
