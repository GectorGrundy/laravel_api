<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeo;

use App\Http\Requests\Request;

class GetByIdRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|numeric',
            'with' => 'array',
            'with.*' => 'in:user_group_target_geo',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('target_geo.on_get_error');
    }
}
