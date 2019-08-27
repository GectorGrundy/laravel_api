<?php
declare(strict_types=1);

namespace App\Http\Requests\Manager;

use Auth;
use App\Http\Requests\Request;
use App\Models\User;

class ChangeProfileRequest extends Request
{
    public function rules()
    {
        $rules = [
            'full_name' => 'present|string|max:255',
            'skype' => 'present|string|max:255',
            'telegram' => 'present|string|max:255',
            'phone' => 'present|string|max:16',
        ];

        if (Auth::user()->isAdmin()) {
            $rules['user_hash'] = 'required|exists:users,hash,role,' . User::MANAGER;
        }

        return $rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_change_profile_error');
    }
}
