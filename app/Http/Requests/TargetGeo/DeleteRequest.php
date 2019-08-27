<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeo;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:target_geo,id,deleted_at,NULL,is_default,0'
        ];
    }

    public function messages()
    {
        return [
            'id.exists' => trans('target_geo.id.exists'),
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('target_geo.on_delete_error');
    }
}
