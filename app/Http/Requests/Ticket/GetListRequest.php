<?php
declare(strict_types=1);

namespace App\Http\Requests\Ticket;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'with' => 'array',
        ];

        if (\Auth::user()->isAdmin()) {
            $rules['with.*'] = 'in:user.group,last_message_user.profile,responsible_user';
        } else {
            $rules['with.*'] = 'in:last_message_user.profile';
        }

        return $rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('tickets.on_get_list_error');
    }
}
