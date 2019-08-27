<?php
declare(strict_types=1);

namespace App\Http\Requests\BalanceTransaction;

use App\Http\Requests\Request;
use App\Models\BalanceTransaction;
use App\Models\Currency;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'date_from' => 'string|date_format:Y-m-d',
            'date_to' => 'string|date_format:Y-m-d',
            'currency_ids' => 'array',
            'currency_ids.*' => 'exists:currencies,id',
            'users_ids' => 'exists:users,id',
            'offer_hashes' => 'array',
            'offer_hashes.*' => 'string',
            'country_ids' => 'array',
            'country_ids.*' => 'string',
            'search_field' => 'in:transaction_hash,lead_hash',
            'search' => 'string',
            'types' => 'required|array|min:1',
            'types.*' => 'in:' . implode(',', [
                    BalanceTransaction::ADVERTISER_HOLD,
                    BalanceTransaction::ADVERTISER_UNHOLD,
                    BalanceTransaction::ADVERTISER_DEPOSIT,
                    BalanceTransaction::ADVERTISER_WRITE_OFF,
                    BalanceTransaction::ADVERTISER_CANCEL,
                    BalanceTransaction::PUBLISHER_HOLD,
                    BalanceTransaction::PUBLISHER_UNHOLD,
                    BalanceTransaction::PUBLISHER_CANCEL,
                    BalanceTransaction::PUBLISHER_WITHDRAW,
                    BalanceTransaction::PUBLISHER_WITHDRAW_CANCEL,
                ]),
            'group_by' => 'in:currency'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('balance_transactions.on_get_list_error');
    }
}
