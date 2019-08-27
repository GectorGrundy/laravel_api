<?php
declare(strict_types=1);

namespace App\Http\Requests\ComebackerAudio;

use App\Http\Requests\Request;
use App\Models\ComebackerAudio;

class CreateRequest extends Request
{
	public function authorize()
	{
		return true;
	}

    public function rules()
    {
        return array_merge(ComebackerAudio::$rules, [
            'audion_path' => 'required|string'
        ]);
    }

	public function messages()
	{
		return [];
	}

	protected function getFailedValidationMessage()
	{
		return trans('comebacker_audio.on_create_error');
	}
}
