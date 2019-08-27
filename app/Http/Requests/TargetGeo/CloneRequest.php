<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeo;

use App\Http\Requests\Request;

class CloneRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:target_geo,id',
            'country_id' => 'required|exists:countries,id',
            'clone_rules' => 'required|in:0,1',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('target_geo.on_clone_error');
    }
}
