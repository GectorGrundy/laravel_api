<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Http\Requests\Request;

class SyncOfferSourcesRequest extends Request
{
	public function rules()
	{
		return [
			'id' => 'required|exists:offers,id',
			'offer_sources' => 'array',
			'offer_sources.*' => 'exists:offer_sources,id',
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('offers.on_sync_offer_sources_error');
	}
}