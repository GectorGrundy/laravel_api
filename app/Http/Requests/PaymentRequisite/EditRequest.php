<?php
declare(strict_types=1);

namespace App\Http\Requests\PaymentRequisite;

use App\Http\Requests\Request;
use Auth;

class EditRequest extends Request
{
    public function rules(): array
    {
        return [
            'wmr' => 'present|nullable|regex:~^R[0-9]{12}$~i|unique:webmoney_requisites,purse,' . Auth::id() . ',user_id',
            'wmz' => 'present|nullable|regex:~^Z[0-9]{12}$~i|unique:webmoney_requisites,purse,' . Auth::id() . ',user_id',
            'wme' => 'present|nullable|regex:~^E[0-9]{12}$~i|unique:webmoney_requisites,purse,' . Auth::id() . ',user_id',
            'paxum' => 'present|nullable|email|unique:paxum_requisites,email,' . Auth::id() . ',user_id',
            'epayments' => 'present|nullable|regex:~^000-[0-9]{6}$~i|unique:epayments_requisites,ewallet,' . Auth::id() . ',user_id',
            'card_number' => 'present|numeric|min:1000000000000000|max:9999999999999999|unique:swift_requisites,card_number,' . Auth::id() . ',user_id',
            'card_holder' => 'present|required_with:card_number|string',
            'expires' => 'present|required_with:card_number|string',
            'birthday' => 'present|required_with:card_number|date',
            'document' => 'present|required_with:card_number|string',
            'country' => 'present|required_with:card_number|string',
            'street' => 'present|required_with:card_number|string',
            'phone' => 'present|required_with:card_number|string',
            'tax_id' => 'present|required_with:card_number|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'card_number.min' => trans('payment_requisites.card_number.incorrect'),
            'card_number.max' => trans('payment_requisites.card_number.incorrect'),
            'paxum.email' => trans('payment_requisites.paxum.email'),
            'epayments.regex' => trans('payment_requisites.epayments.regex'),
        ];
    }

    protected function getFailedValidationMessage(): string
    {
        return trans('payment_requisites.on_edit_error');
    }
}

