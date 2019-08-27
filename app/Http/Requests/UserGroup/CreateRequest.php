<?php
declare(strict_types=1);

namespace App\Http\Requests\UserGroup;

use App\Http\Requests\Request;

class CreateRequest extends Request
{
    public function rules()
    {
        return [
            'title' => 'required|unique:user_groups,title,NULL,id,deleted_at,NULL',
            'description' => 'present',
            'color' => ['required', 'regex:~[0-9abcdefgh]{3,6}~i'],
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('user_groups.on_create_error');
    }
}
