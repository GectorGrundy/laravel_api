<?php
declare(strict_types=1);

namespace App\Http\Requests\UserGroup;

use App\Http\Requests\Request;

class GetByIdRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|numeric',
            'with' => 'array',
            'with.*' => 'in:users',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('user_groups.on_get_error');
    }
}
