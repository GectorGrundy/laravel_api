<?php
declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class ChangeProfileRequest extends Request
{
    public function rules()
    {
        return [
            'full_name' => 'present|string',
            'skype' => 'present|string',
            'telegram' => 'present|string',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_change_profile_error');
    }
}
