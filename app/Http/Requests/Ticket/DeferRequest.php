<?php
declare(strict_types=1);

namespace App\Http\Requests\Ticket;

use App\Models\User;
use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class DeferRequest extends Request
{
    public function rules()
    {
        return [
            'hash' => 'required|exists:tickets,hash',
            'deferred_until_at' => 'required|date_format:Y-m-d H:i:s',
            'responsible_user_id' => [
                'required',
                Rule::exists('users', 'id')->whereIn('role', [User::ADMINISTRATOR])
            ],

        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('tickets.on_defer_error');
    }
}
