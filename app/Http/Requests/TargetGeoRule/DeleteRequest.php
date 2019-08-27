<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeoRule;

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
			'id' => 'required|exists:target_geo_rules,id,deleted_at,NULL'
		];
	}

	public function messages()
	{
		return [];
	}

	protected function getFailedValidationMessage()
	{
		return trans('target_geo_rules.on_delete_error');
	}
}
