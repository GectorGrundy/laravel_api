<?php
declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class LoginRequest extends Request
{
    public function rules(): array
    {
        return [
            'email' => 'required',
            'password' => 'required|string',
            'remember' => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => trans('auth.email.required'),
            'email.email' => trans('auth.email.email'),
            'password.required' => trans('auth.password.required'),
        ];
    }

    protected function getFailedValidationMessage(): string
    {
        return trans('auth.on_login_validate_error');
    }
}
