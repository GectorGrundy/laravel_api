<?php
declare(strict_types=1);

namespace App\Http\Requests\Ticket;

use App\Http\Requests\Request;

class GetByHashRequest extends Request
{
    public function rules()
    {
        return [
            'hash' => 'required|exists:tickets,hash',
            'with' => 'array',
            'with.*' => 'in:' . implode(',', [
                    'user',
                    'messages.user',
                    'messages.user.profile',
                    'messages.user.group'
                ])
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('tickets.on_get_error');
    }
}
