<?php
declare(strict_types=1);

namespace App\Http\Requests\UserGroup;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:user_groups,id,deleted_at,NULL',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('user_groups.on_delete_error');
    }
}
