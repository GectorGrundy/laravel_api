<?php
declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

use App\Models\User;
use Auth;
use Hash;
use Illuminate\Contracts\Validation\Validator;

class ChangePasswordRequest extends Request
{
    public function rules()
    {
        return [
            'password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ];
    }

    public function messages()
    {
        return [
            'password.required' => trans('users.password.required'),
            'password.min' => trans('users.password.min'),
            'new_password.required' => trans('users.new_password.required'),
            'new_password.min' => trans('users.new_password.min'),
            'new_password.confirmed' => trans('users.password.confirmed'),
            'password_confirmation.required' => trans('users.password_confirmation.required'),
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $user = User::find(Auth::user()->id);

            if (!Hash::check($this->input('password'), $user->password)) {
                $validator->errors()->add('password', trans('users.password.incorrect'));
            }
        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_change_passoword_error');
    }
}
