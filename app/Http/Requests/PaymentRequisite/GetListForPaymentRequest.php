<?php
declare(strict_types=1);

namespace App\Http\Requests\PaymentRequisite;

use App\Http\Requests\Request;
use App\Models\Currency;
use App\Models\User;

class GetListForPaymentRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'currency_id' => 'required|in:' . Currency::PAYOUT_CURRENCIES_STR,
        ];

        if (\Auth::user()->isAdmin()) {
            $rules['publisher_id'] = 'required|exists:users,id,role,' . User::PUBLISHER;
        }

        return $rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('payment_requisites.on_get_list_for_payment_error');
    }
}
