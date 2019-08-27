<?php
declare(strict_types=1);

namespace App\Http\Requests\UserGroupTargetGeo;

use App\Http\Requests\Request;
use App\Models\Currency;
use Illuminate\Contracts\Validation\Validator;

class SyncRequest extends Request
{
    public function rules()
    {
        return [
            'target_geo_id' => 'required|numeric|exists:target_geo,id',
            'stakes' => 'required',
            'stakes.*.user_group_id' => 'required|exists:user_groups,id',
            'stakes.*.payout' => 'required|numeric|min:0',
            'stakes.*.currency_id' => 'required|in:' . Currency::PAYOUT_CURRENCIES_STR,
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('user_group_target_geo.on_sync_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {

            if ($this->stakesHaveMoreThanOneDefault()) {
                $validator->errors()->add('stakes.is_default', trans('user_group_target_geo.stake_must_have_one_default'));
            }
        });
    }

    private function stakesHaveMoreThanOneDefault()
    {
        return collect($this->input('stakes', []))->where('is_default', 1)->count() > 1;
    }
}
