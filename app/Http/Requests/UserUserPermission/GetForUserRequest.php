<?php
declare(strict_types=1);

namespace App\Http\Requests\UserUserPermission;

use App\Http\Requests\Request;

class GetForUserRequest extends Request
{
    public function rules()
    {
        return [
            'user_hash' => 'required|exists:users,hash',
        ];
    }
}
