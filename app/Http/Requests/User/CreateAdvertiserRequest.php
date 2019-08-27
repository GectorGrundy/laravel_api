<?php
declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use App\Models\Currency;
use App\Http\Requests\Request;

class CreateAdvertiserRequest extends Request
{
    public function rules()
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'info' => 'present|max:255',
            'accounts' => 'present|array',
            'accounts.*' => 'in:' . Currency::PAYOUT_CURRENCIES_STR,
            'full_name' => 'present|string|max:255',
            'skype' => 'present|string|max:255',
            'telegram' => 'present|string|max:255',
            'phone' => 'present|string|max:16',
            'whatsapp' => 'present|string|max:16',
            'manager_id' => 'nullable|exists:users,id,role,' . User::MANAGER
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_create_error');
    }
}
