<?php
declare(strict_types=1);

namespace App\Http\Requests\Account;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'user_id' => 'required|numeric|min:0',
            'with' => 'array',
            'with.*' => 'in:currency'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('accounts.on_get_list_error');
    }
}
