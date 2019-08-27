<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeoIntegration;

use App\Http\Requests\Request;
use App\Models\TargetGeoIntegration;
use Illuminate\Contracts\Validation\Validator;

class CreateRequest extends Request
{
    public function rules(): array
    {
        return array_merge(TargetGeoIntegration::$rules, [
            'currency_id' => 'required|exists:accounts,currency_id,user_id,' . $this->advertiser_id,
            'target_geo_id' => 'required|exists:target_geo,id,deleted_at,NULL|unique:target_geo_integrations,target_geo_id',
        ]);
    }

    protected function getFailedValidationMessage()
    {
        return trans('target_geo_integrations.on_create_error');
    }
}
