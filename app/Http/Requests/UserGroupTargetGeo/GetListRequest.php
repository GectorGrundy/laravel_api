<?php
declare(strict_types=1);

namespace App\Http\Requests\UserGroupTargetGeo;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'target_geo_id' => 'required|numeric|exists:target_geo,id',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('user_group_target_geo.on_get_list_error');
    }
}
