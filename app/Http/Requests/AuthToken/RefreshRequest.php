<?php
declare(strict_types=1);

namespace App\Http\Requests\AuthToken;

use App\Http\Requests\Request;

class RefreshRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('auth.on_login_validate_error');
    }
}
