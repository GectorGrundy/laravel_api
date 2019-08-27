<?php
declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class PasswordResetRequest extends Request
{
    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8',
            'token' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'email.exists' => trans('auth.email.exists'),
            'password.min' => trans('auth.password.min'),
            'token.exists' => trans('auth.token.exists'),
        ];
    }

    /**
     * Get message on validation error
     *
     */
    protected function getFailedValidationMessage()
    {
        return trans('auth.on_recovery_password_validate_error');
    }
}
