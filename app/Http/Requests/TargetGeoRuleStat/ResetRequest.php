<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeoRuleStat;

use App\Http\Requests\Request;

class ResetRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'target_geo_id' => 'required|exists:target_geo,id,deleted_at,NULL'
        ];
    }

    public function messages()
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('target_geo_rules.on_create_error');
    }
}
