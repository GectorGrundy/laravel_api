<?php
declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Exceptions\User\UnknownUserRole;
use App\Http\Requests\Request;
use App\Models\User;

class LoginAsUserRequest extends Request
{
    public function authorize()
    {
        $user = \Auth::user();
        switch ($user['role']) {
            case User::ADMINISTRATOR:
                $accessible_role = [User::PUBLISHER, User::ADVERTISER, User::SUPPORT, User::MANAGER];
                break;

            case User::SUPPORT:
                $accessible_role = [User::PUBLISHER];
                break;

            case User::MANAGER:
                $accessible_role = [User::ADVERTISER];
                break;

            default:
                throw new UnknownUserRole($user['role']);
        }

        return User::where('hash', $this->input('user_hash'))
            ->where('hash', '!=', $user['hash'])
            ->whereIn('role', $accessible_role)
            ->exists();
    }

    public function rules()
    {
        return [
            'user_hash' => 'required|string'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('auth.on_enter_in_user_cabinet_error');
    }
}
