<?php
declare(strict_types=1);

namespace App\Http\Requests\Advertiser;

use App\Http\Requests\Request;

class GetByHashRequest extends Request
{
    public function rules()
    {
        return [
            'hash' => 'required|string',
            'with' => 'array',
            'with.*' => 'in:accounts,profile',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_get_error');
    }
}
