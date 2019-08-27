<?php
declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Http\Requests\Request;
use App\Models\{
    Currency, Payment, PaymentSystem
};

class GetListRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'status' => 'in:' . implode(',', [
                    Payment::PENDING, Payment::ACCEPTED, Payment::CANCELLED, Payment::PAID
                ]),
            'currency_ids' => 'array',
            'currency_ids.*' => 'in:' . Currency::PAYOUT_CURRENCIES_STR,
            'payment_systems' => 'array',
            'payment_systems.*' => 'in:' . implode(',', [
                    PaymentSystem::WEBMONEY, PaymentSystem::EPAYMENTS, PaymentSystem::PAXUM
                ]),
            'per_page' => 'numeric|min:0|max:200',
            'page' => 'numeric|min:0',
        ];

        if (\Auth::user()->isAdmin()) {
            $rules['with'] = 'array';
            $rules['with.*'] = 'in:processed_user,user.publisher,paid_user';
            $rules['publisher_hashes'] = 'array';
        }

        return $rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('payments.on_get_list_error');
    }
}
