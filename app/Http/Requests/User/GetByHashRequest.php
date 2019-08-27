<?php
declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class GetByHashRequest extends Request
{
    public function rules()
    {
        return [
            'user_hash'=> 'required|string'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_get_error');
    }
}
