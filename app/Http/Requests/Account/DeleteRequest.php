<?php
declare(strict_types=1);

namespace App\Http\Requests\Account;

use App\Models\{
    Account, TargetGeoRule
};
use App\Http\Requests\Request;
use Illuminate\Contracts\Validation\Validator;

class DeleteRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|numeric|exists:accounts'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('accounts.on_delete_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $account = Account::find($this->get('id'));

            if ($this->accountHasTargetGeoRules($account)) {
                $validator->errors()->add('has_geo_rules', trans('accounts.has_geo_rules_error'));
            }

            if ($this->isNotUsedAccount($account)) {
                $validator->errors()->add('not_empty', trans('accounts.not_used_account_error'));
            }

            if ($this->isLastForUser($account)) {
                $validator->errors()->add('last', trans('accounts.cant_remove_last_account'));
            }
        });
    }

    private function isNotUsedAccount(Account $account)
    {
        return (float)$account['balance'] || (float)$account['hold'] || (float)$account['system_balance'];
    }

    private function isLastForUser(Account $account): bool
    {
        return Account::where('user_id', $account['user_id'])->active()->count() < 2;
    }

    private function accountHasTargetGeoRules(Account $account): bool
    {
        return TargetGeoRule::where('advertiser_id', $account['user_id'])
            ->where('currency_id', $account['currency_id'])
            ->exists();
    }
}
