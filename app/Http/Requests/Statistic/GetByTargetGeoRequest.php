<?php
declare(strict_types=1);

namespace App\Http\Requests\Statistic;

use App\Http\Requests\Request;
use App\Models\Currency;
use Illuminate\Contracts\Validation\Validator;

class GetByTargetGeoRequest extends Request
{
    public function rules()
    {
        return [
            'target_hash' => 'required|string',
            'country_id.*' => 'exists:countries,id',
            'currency_ids' => 'array',
            'currency_ids.*' => 'in:' . implode(',', array_merge([0], Currency::PAYOUT_CURRENCIES)),
            'group_by' => 'required|in:created_at,processed_at',
            'search_field' => 'in:publisher_hash,hash',
            'search' => 'string',
            'sorting' => 'required|in:asc,desc',
            'sort_by' => 'required|in:' . implode(',', [
                    'id','real_approve', 'approve', 'total_count', 'approved_count', 'held_count', 'cancelled_count',
                    'trashed_count', 'rub_approved_payout', 'usd_approved_payout', 'eur_approved_payout',
                    'rub_held_payout', 'usd_held_payout', 'eur_held_payout'
                ])
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('statistics.on_get_list_error');
    }
}
