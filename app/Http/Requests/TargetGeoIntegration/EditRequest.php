<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeoIntegration;

use App\Http\Requests\Request;
use App\Models\TargetGeoIntegration;

class EditRequest extends Request
{
    public function rules(): array
    {
        return array_merge(TargetGeoIntegration::$rules, [
            'id' => 'required|numeric|exists:target_geo_integrations',
            'currency_id' => 'required|exists:accounts,currency_id,user_id,' . $this->advertiser_id,
        ]);
    }

    protected function getFailedValidationMessage()
    {
        return trans('target_geo_integrations.on_create_error');
    }
}
