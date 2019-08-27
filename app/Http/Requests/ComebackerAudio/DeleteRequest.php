<?php
declare(strict_types=1);

namespace App\Http\Requests\ComebackerAudio;

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
            'id' => 'required|exists:comebacker_audio,id'
        ];
    }

    public function messages()
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('comebacker_audio.on_delete_error');
    }
}
