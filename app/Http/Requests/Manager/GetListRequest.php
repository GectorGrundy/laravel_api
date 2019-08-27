<?php
declare(strict_types=1);

namespace App\Http\Requests\Manager;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'page' => 'numeric|min:0',
            'per_page' => 'numeric|min:0|max:100',
            'with' => 'array',
            'with.*' => 'in:profile',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_get_list_error');
    }
}
