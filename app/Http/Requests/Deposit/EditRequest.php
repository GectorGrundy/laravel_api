<?php
declare(strict_types=1);

namespace App\Http\Requests\Deposit;

use App\Http\Requests\Request;

class EditRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:deposits,id',
            'replenishment_method' => 'required|in:cash,swift,epayments,webmoney,paxum,privat24,bitcoin,other',
            'description' => 'string',
            'with' => 'array',
            'with.*' => 'in:advertiser,advertiser.profile,admin',
            'created_at' => 'date_format:Y-m-d H:i:s'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('deposits.on_edit_error');
    }
}
