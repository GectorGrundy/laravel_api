<?php
declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Http\Requests\Request;
use App\Models\Payment;

class AcceptRequest extends Request
{
    public function rules()
    {
        return [
            'hash' => 'required|exists:payments,hash,status,' . Payment::PENDING,
            'description' => 'present|max:255',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('payments.on_accept_error');
    }
}
