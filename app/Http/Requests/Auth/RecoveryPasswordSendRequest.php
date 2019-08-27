<?php
declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class RecoveryPasswordSendRequest extends Request
{
    public function rules()
    {
        return [
            'email' => 'required|exists:users,email'
        ];
    }

    public function messages()
    {
        return [
            'email.exists' => trans('auth.email.exists'),
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('auth.on_recovery_password_validate_error');
    }
}
