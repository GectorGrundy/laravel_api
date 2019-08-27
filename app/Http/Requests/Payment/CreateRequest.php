<?php
declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Http\Requests\Request;
use App\Models\Currency;
use App\Models\PaymentRequisite;
use App\Models\PaymentSystem;
use App\Models\Publisher;
use App\Models\PublisherProfile;
use App\Models\User;
use Auth;
use function GuzzleHttp\default_ca_bundle;
use Hashids;
use Illuminate\Contracts\Validation\Validator;

class CreateRequest extends Request
{
    public function rules()
    {
        $rules = [
            'payment_system_id' => 'required|exists:payment_systems,id',
            'requisite_hash' => 'required|string',
            'currency_id' => 'required|in:' . Currency::PAYOUT_CURRENCIES_STR,
            'payout' => 'required|numeric',
        ];

        if (\Auth::user()->isAdmin()) {
            $rules['with'] = 'array';
            $rules['with.*'] = 'in:processed_user,user.publisher,paid_user';
            $rules['publisher_id'] = 'required|exists:users,id,role,' . User::PUBLISHER;
        } else {
            $rules['with'] = 'array';
            $rules['with.*'] = 'not_in:processed_user,user.publisher,paid_user';
        }

        return $rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('payments.on_create_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {

            // Validate payment_system_id
            $payment_system = PaymentSystem::find($this->input('payment_system_id'));
            if (\is_null($payment_system)) {
                $validator->errors()->add('payment_system_id', trans('validation.exists', [
                    'attribute' => 'payment_system_id'
                ]));
            }

            // Validate requisite_id
            $requisite = PaymentRequisite::getByHash($payment_system, $this->input('requisite_hash'));
            if (\is_null($requisite) || $requisite['payment_system_id'] !== $payment_system['id']) {
                $validator->errors()->add('requisite', trans('payments.requisite_hash.exists'));
            }

            // Validate check min sum payment of payment system and user balance
            $payout = (float)$this->input('payout');
            $publisher_balance = $this->getBalanceByCurrencyId(
                $this->getPaymentPublisher(),
                (int)$this->input('currency_id')
            );
            if ($payout < $payment_system->min_payout) {
                $validator->errors()->add('payout', trans('payments.payout.payout_is_less'));
            } else if ($payout > $publisher_balance) {
                $validator->errors()->add('payout', trans('payments.payout.incorrect_balance'));
            }

            $this->merge([
                'payment_system' => $payment_system,
                'requisite' => $requisite,
            ]);
        });
    }

    private function getBalanceByCurrencyId(User $user, int $currency_id)
    {
        switch ($currency_id) {
            case Currency::RUB_ID:
                return $user->profile->balance_rub;

            case Currency::USD_ID:
                return $user->profile->balance_usd;

            case Currency::EUR_ID:
                return $user->profile->balance_eur;
        }
    }

    private function getPaymentPublisher(): User
    {
        $current_user = \Auth::user();
        if ($current_user->isAdmin()) {
            $publisher = User::find($this->input('publisher_id'));
        } else {
            $publisher = $current_user;
        }

        User::$role = User::PUBLISHER;
        $publisher->load('profile');

        return $publisher;
    }
}
