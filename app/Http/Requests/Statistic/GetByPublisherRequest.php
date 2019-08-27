<?php
declare(strict_types=1);

namespace App\Http\Requests\Statistic;

use App\Http\Requests\Request;
use App\Models\Currency;
use Illuminate\Contracts\Validation\Validator;

class GetByPublisherRequest extends Request
{
    public function rules()
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
            'with.*' => 'in:country',
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {
            if (\Auth::user()->isAdvertiser()) {
                $rules = [
                    'currency_ids' => 'array',
                    'currency_ids.*' => 'in:' . implode(',', array_merge([0], Currency::PAYOUT_CURRENCIES)),
                    'group_by' => 'required|in:created_at,processed_at',
                    'search_field' => 'in:publisher_hash,hash',
                    'search' => 'string',
                    'sorting' => 'required|in:asc,desc',
                    'sort_by' => 'required|in:' . implode(',', [
                            'id', 'real_approve', 'approve', 'total_count', 'approved_count', 'held_count',
                            'cancelled_count', 'trashed_count', 'rub_approved_payout', 'usd_approved_payout',
                            'eur_approved_payout', 'rub_held_payout', 'usd_held_payout', 'eur_held_payout'
                        ])
                ];
            } else {
                $rules = [
                    'currency_id' => 'required|in:' . implode(',', Currency::PAYOUT_CURRENCIES),
                ];
            }

            $new_validator = \Validator::make($this->all(), $rules);

            if ($new_validator->fails()) {
                foreach ($new_validator->errors()->messages() as $error_field => $error) {
                    $validator->errors()->add($error_field, $error);
                }
            }
        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('statistics.on_get_list_error');
    }
}
