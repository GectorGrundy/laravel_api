<?php
declare(strict_types=1);

namespace App\Http\Requests\UserGroup;

use App\Http\Requests\Request;

class EditRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:user_groups,id,deleted_at,NULL',
            'title' => 'required|unique:user_groups,title,' . $this->input('id') . ',id,deleted_at,NULL',
            'description' => 'present',
            'color' => ['required', 'regex:~[0-9abcdefgh]{3,6}~i'],
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('user_groups.on_edit_error');
    }
}
