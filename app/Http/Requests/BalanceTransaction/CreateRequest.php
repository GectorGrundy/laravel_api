<?php
declare(strict_types=1);

namespace App\Http\Requests\BalanceTransaction;

use App\Http\Requests\Request;
use App\Models\BalanceTransaction;
use App\Models\Currency;
use Illuminate\Contracts\Validation\Validator;
use App\Models\User;

class CreateRequest extends Request
{
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id,role,' . User::ADVERTISER,
            'type' => 'required|in:' . BalanceTransaction::ADVERTISER_WRITE_OFF,
            'currency_id' => 'required|exists:currencies,id',
            'balance_sum' => 'required|numeric|min:0.01',
            'description' => 'present|string'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('balance_transactions.on_create_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {

            /**
             * @var User $user
             */
            $user = User::find($this->input('user_id'));
            if (\is_null($user) || !$user->isAdvertiser()) {
                $validator->errors()->add('user_id', trans('validation.not_in', ['attribute' => 'user_id']));
            }

            $this->merge([
                'user' => $user,
            ]);
        });

    }
}
