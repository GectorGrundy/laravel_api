<?php
declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Http\Requests\Request;
use App\Models\Scopes\GlobalUserEnabledScope;

class LogoutAsUserRequest extends Request
{
    public function authorize()
    {
        $user = \Auth::user();

        return User::withoutGlobalScope(GlobalUserEnabledScope::class)
            ->where('hash', $this->input('foreign_user_hash'))
            ->where('hash', '!=', $user['hash'])
            ->whereIn('role', [User::SUPPORT, User::ADMINISTRATOR, User::MANAGER])
            ->exists();
    }

    public function rules()
    {
        return [
            'foreign_user_hash' => 'required'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('auth.on_return_in_admin_cabinet_error');
    }
}
