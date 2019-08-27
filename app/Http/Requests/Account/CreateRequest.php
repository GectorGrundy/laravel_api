<?php
declare(strict_types=1);

namespace App\Http\Requests\Account;

use App\Http\Requests\Request;
use App\Models\Currency;
use App\Models\User;

class CreateRequest extends Request
{
    public function rules()
    {
        return [
            'user_id' => [
                'required',
                'numeric',
                'exists:users,id,role,' . User::ADVERTISER,
                'unique:accounts,user_id,NULL,id,currency_id,' . $this->input('currency_id') . ',is_active,1',
            ],
            'currency_id' => [
                'required',
                'numeric',
                'in:' . Currency::RUB_ID . ',' . Currency::EUR_ID . ',' . Currency::USD_ID,
                'unique:accounts,currency_id,NULL,id,user_id,' . $this->input('user_id') . ',is_active,1',
            ],
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('accounts.on_create_error');
    }
}
