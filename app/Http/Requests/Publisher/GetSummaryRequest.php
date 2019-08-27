<?php
declare(strict_types=1);

namespace App\Http\Requests\Publisher;

use App\Http\Requests\Request;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;

class GetSummaryRequest extends Request
{
    private $validator;

    public function rules()
    {
        return [
            'search_field' => 'in:id,hash,email,phone,skype,telegram,balance,hold',
            'search' => 'string',
            'group_ids' => 'sometimes|array|min:1',
            'group_ids.*' => 'numeric',
            'status' => 'in:' . User::LOCKED . ',' . User::ACTIVE,
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {
            $this->validator = $validator;

            //@todo Write validation rule for these
            $this->validateBalanceSearchField();
            $this->validateHoldSearchField();
        });
    }

    private function validateBalanceSearchField()
    {
        if ($this->input('search_field') === 'balance') {
            $validator = \Validator::make($this->all(), [
                'currency_id' => 'required|in:' . Currency::PAYOUT_CURRENCIES_STR,
                'constraint' => 'required|in:less,more',
            ]);

            if ($validator->fails()) {
                return addErrorsToValidator($validator, $this->validator);
            }
        }
    }

    private function validateHoldSearchField()
    {
        if ($this->input('search_field') === 'hold') {
            $validator = \Validator::make($this->all(), [
                'currency_id' => 'required|in:' . Currency::PAYOUT_CURRENCIES_STR,
                'constraint' => 'required|in:less,more',
            ]);

            if ($validator->fails()) {
                return addErrorsToValidator($validator, $this->validator);
            }
        }
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_get_list_error');
    }
}
