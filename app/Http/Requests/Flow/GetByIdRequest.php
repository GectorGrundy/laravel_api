<?php
declare(strict_types=1);

namespace App\Http\Requests\Flow;

use App\Http\Requests\Request;
use App\Models\Flow;
use Auth;

class GetByIdRequest extends Request
{
	public function rules()
	{
		return [
			'id' => 'required|exists:flows,id',
		];
	}

    protected function getFailedValidationMessage()
    {
        return trans('flows.on_get_error');
    }

	public function moreValidation($validator)
	{
		$validator->after(function ($validator) {
			$flow_info = Flow::find($this->get('id'));

			if ('publisher' === Auth::user()->role && $flow_info->publisher_id != Auth::user()->id) {
				$validator->errors()->add('id', trans('auth.forbidden'));
			}

			$this->merge([
				'flow_info' => $flow_info
			]);
		});
	}
}
