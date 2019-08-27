<?php
declare(strict_types=1);

namespace App\Http\Requests\File;

use App\Http\Requests\Request;
use Validator;

class UploadImageRequest extends Request
{
    public function moreValidation($validator)
    {
        $validator->after(function ($validator) {
            $file = ['preview' => $this->file('preview')];
            $rules = ['preview' => 'required|mimes:png'];

            $new_validator = Validator::make($file, $rules);
            if ($new_validator->fails()) {
                $messages = $new_validator->errors();

                $validator->errors()->add('preview', $messages->first('preview'));
            }
        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('messages.on_upload_image_error');
    }
}
