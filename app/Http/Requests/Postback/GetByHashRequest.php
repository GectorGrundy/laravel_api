<?php
declare(strict_types=1);

namespace App\Http\Requests\Postback;

use App\Http\Requests\Request;

class GetByHashRequest extends Request
{
    public function rules()
    {
        return [
            'postback_hash' => 'required|exists:postbacks,hash'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('postbacks.on_get_error');
    }
}
