<?php
declare(strict_types=1);

namespace App\Http\Requests\ComebackerAudio;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'with' => 'array',
			'with.*' => 'in:locale'
		];
	}

	public function messages()
	{
		return [];
	}

	protected function getFailedValidationMessage()
	{
		return trans('comebacker_audio.on_get_list_error');
	}
}
