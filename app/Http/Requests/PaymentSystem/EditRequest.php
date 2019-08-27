<?php
declare(strict_types=1);

namespace App\Http\Requests\PaymentSystem;

use App\Http\Requests\Request;

class EditRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:payment_systems,id',
            'title' => 'required|max:255',
            'status' => 'required|in:active,stopped',
            'percentage_comission' => 'required|numeric',
            'fixed_comission' => 'required|numeric',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('countries.on_edit_error');
    }
}
