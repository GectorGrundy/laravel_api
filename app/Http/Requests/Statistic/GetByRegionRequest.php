<?php
declare(strict_types=1);

namespace App\Http\Requests\Statistic;

use App\Models\Currency;
use App\Http\Requests\Request;

class GetByRegionRequest extends Request
{
	public function rules(): array
    {
		return [
			'flow_hashes' => 'array',
			'flow_hashes.*' => 'exists:flows,hash,publisher_id,' . \Auth::user()->id,
			'offer_hashes' => 'array',
			'offer_hashes.*' => 'exists:offers,hash',
			'landing_hashes' => 'array',
			'landing_hashes.*' => 'exists:landings,hash',
            'country_ids' => 'array',
            'country_ids.*' => 'numeric',
            'target_geo_country_ids' => 'array',
            'target_geo_country_ids.*' => 'numeric',
            'currency_id' => 'required|in:' . implode(',', Currency::PAYOUT_CURRENCIES),
			'with.*' => 'in:region',
		];
	}

	protected function getFailedValidationMessage(): string
	{
		return trans('statistics.on_get_list_error');
	}
}
