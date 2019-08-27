<?php
declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Http\Requests\Request;
use App\Models\Payment;

class CancelRequest extends Request
{
    public function rules()
    {
        $rules = [
            'hash' => 'required|exists:payments,hash,status,' . Payment::PENDING,
        ];

        if (\Auth::user()->isAdmin()) {
            $rules['description'] = 'present|max:255';
        }

        return $rules;

    }

    protected function getFailedValidationMessage()
    {
        return trans('payments.on_cancel_error');
    }
}
