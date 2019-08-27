<?php
declare(strict_types=1);

namespace App\Http\Requests\UserUserPermission;

use App\Http\Requests\Request;

class SyncRequest extends Request
{
    public function rules()
    {
        return [
            'user_hash' => 'required|exists:users,hash',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:user_permissions,id',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('user_user_permissions.on_sync_error');
    }
}
