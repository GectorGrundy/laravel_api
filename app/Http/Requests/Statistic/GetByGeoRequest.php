<?php
declare(strict_types=1);

namespace App\Http\Requests\Statistic;

use App\Http\Requests\Request;

use App\Models\Currency;
use Auth;

class GetByGeoRequest extends Request
{
    public function rules()
    {
        return [
            'flow_hashes' => 'array',
            'offer_hashes' => 'array',
            'landing_hashes' => 'array',
            'transit_hashes' => 'array',
            'country_ids' => 'array',
            'country_ids.*' => 'numeric',
            'target_geo_country_ids' => 'array',
            'target_geo_country_ids.*' => 'numeric',
            'currency_id' => 'required|in:' . implode(',', Currency::PAYOUT_CURRENCIES),
            'with.*' => 'in:country,target_geo_country',
            'group_by' => 'required|in:country_id,target_geo_country_id'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('statistics.on_get_list_error');
    }
}
