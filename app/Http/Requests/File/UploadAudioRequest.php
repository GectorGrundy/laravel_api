<?php
declare(strict_types=1);

namespace App\Http\Requests\File;

use App\Http\Requests\Request;
use Validator;

class UploadAudioRequest extends Request
{
	public function moreValidation($validator)
	{
		$validator->after(function ($validator) {
			$file = ['audio' => $this->file('audio')];
			$rules = ['audio' => 'required|mimetypes:audio/mpeg'];

			$new_validator = Validator::make($file, $rules);
			if ($new_validator->fails()) {
				$messages = $new_validator->errors();

				$validator->errors()->add('audio', $messages->first('audio'));
			}
		});
	}

	protected function getFailedValidationMessage()
	{
		return trans('messages.on_upload_audio_error');
	}
}
