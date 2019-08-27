<?php
declare(strict_types=1);

namespace App\Http\Requests\BalanceTransaction;

use App\Http\Requests\Request;
use App\Models\BalanceTransaction;
use App\Models\Currency;
use Illuminate\Contracts\Validation\Validator;
use App\Models\User;

class EditRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:balance_transactions,id',
            'description' => 'present|string'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('balance_transactions.on_edit_error');
    }
}
