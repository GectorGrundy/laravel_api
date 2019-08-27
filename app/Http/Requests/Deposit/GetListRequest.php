<?php
declare(strict_types=1);

namespace App\Http\Requests\Deposit;

use App\Http\Requests\Request;
use App\Models\Currency;

class GetListRequest extends Request
{
	public function rules()
	{
		return [
		    'date_from'=> 'date_format:Y-m-d',
		    'date_to'=> 'date_format:Y-m-d',
            'with' => 'array',
            'with.*' => 'in:advertiser,advertiser.profile,admin',
            'advertiser_hashes' => 'array',
            'advertiser_hashes.*' => 'string',
            'currency_ids' => 'array',
            'currency_ids.*' => 'in:' . implode(',', Currency::PAYOUT_CURRENCIES),
        ];
    }

	protected function getFailedValidationMessage()
	{
		return trans('deposits.on_get_list_error');
	}
}
