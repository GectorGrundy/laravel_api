<?php
declare(strict_types=1);

namespace App\Http\Requests\ComebackerAudio;

use App\Http\Requests\Request;
use App\Models\ComebackerAudio;

class EditRequest extends Request
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return array_merge(ComebackerAudio::$rules, [
			'id' => 'required|exists:comebacker_audio,id',
            'audion_path' => 'string'
        ]);
	}

	public function messages()
	{
		return [];
	}

	protected function getFailedValidationMessage()
	{
		return trans('comebacker_audio.on_edit_error');
	}
}
