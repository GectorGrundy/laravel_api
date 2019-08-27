<?php
declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class UpdateStatisticSettingsRequest extends Request
{
	public function rules()
	{
		return [
            'mark_roi' => 'in:0,1',
            'columns' => 'required|array'
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('users.on_update_settings_error');
	}
}
