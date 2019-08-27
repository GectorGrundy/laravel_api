<?php
declare(strict_types=1);

namespace App\Http\Requests\Flow;

use App\Http\Requests\Request;
use App\Models\Currency;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'search_type' => 'in:hash,title',
            'search' => 'string|min:1',
            'per_page' => 'numeric|min:0|max:999',
            'page' => 'numeric|min:0',
            'offer_hashes' => 'array',
            'offer_hashes.*' => 'exists:offers,hash',
            'with' => 'array',
            'with.*' => 'in:' . implode(',', [
                    'offer', 'offer.currency',
                    'transits', 'transits.locale',
                    'landings', 'landings.locale',
                    'day_statistics',
                    'group',
                ]),
            'country_ids' => 'array',
            'country_ids.*' => 'numeric',
            'group_hashes' => 'array',
            'group_hashes.*' => 'string',
            'currency_ids' => 'array',
            'currency_ids.*' => 'in:' . implode(',', Currency::PAYOUT_CURRENCIES),
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('flows.on_get_list_error');
    }
}
