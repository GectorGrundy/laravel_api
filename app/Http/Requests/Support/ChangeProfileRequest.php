<?php
declare(strict_types=1);

namespace App\Http\Requests\Support;

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

        if (Auth::user()['role'] === User::ADMINISTRATOR) {
            $rules['user_id'] = 'required|exists:support_profiles,user_id';
        }

        return $rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_change_profile_error');
    }
}
