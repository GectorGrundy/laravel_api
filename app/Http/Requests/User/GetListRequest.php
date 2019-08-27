<?php
declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        $roles_str = implode(',', [
            User::ADMINISTRATOR, User::PUBLISHER, User::ADVERTISER, User::SUPPORT
        ]);

        return [
            'role' => 'array',
            'role.*' => 'in:' . $roles_str,
            'hashes' => 'array|max:200',
            'hashes.*' => 'string',
            'search' => 'string|max:255',
            'per_page' => 'numeric|min:0|max:50',
            'page' => 'numeric|min:0',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_get_list_error');
    }
}
