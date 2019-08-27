<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeoRule;

use App\Http\Requests\Request;

class EditPriorityRequest extends Request
{
	public function rules()
	{
		return [
			'rules' => 'required|array|min:1',
			'rules.*.id' => 'required|exists:target_geo_rules,id,deleted_at,NULL',
			'rules.*.priority' => 'required|numeric|min:1',
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('target_geo_rules.on_edit_priority_error');
	}
}
