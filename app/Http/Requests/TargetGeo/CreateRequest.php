<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeo;

use App\Http\Requests\Request;
use App\Models\Currency;
use App\Models\TargetGeo;

class CreateRequest extends Request
{
    public function rules(): array
    {
        return array_merge(TargetGeo::$rules, [
            'payout' => 'required|numeric|min:0',
            'payout_currency_id' => 'required|in:' . Currency::PAYOUT_CURRENCIES_STR,
        ]);
    }

    protected function getFailedValidationMessage()
    {
        return trans('target_geo.on_create_error');
    }
}
