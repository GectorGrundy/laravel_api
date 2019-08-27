<?php
declare(strict_types=1);

namespace App\Http\Requests\Transit;

use App\Http\Requests\RealpathRule;
use App\Http\Requests\Request;
use App\Models\Transit;
use Illuminate\Contracts\Validation\Validator;

class CreateRequest extends Request
{
    public function rules()
    {
        return array_merge(Transit::$rules, [
            'thumb_path' => 'required|max:255',
            'subdomain' => 'required|max:255|unique:transits,subdomain|unique:landings,subdomain',
            'realpath' => 'required|string',
        ]);
    }

    public function messages()
    {
        return [
            'thumb_path.required' => trans('messages.thumb_path.required')
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {
            (new RealpathRule())->validate($this, $validator);
        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('transits.on_create_error');
    }
}
