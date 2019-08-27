<?php
declare(strict_types=1);

namespace App\Http\Requests\TicketMessage;

use App\Http\Requests\Request;
use Auth;
use App\Models\Ticket;

class CreateRequest extends Request
{
    public function authorize()
    {
        return Ticket::where('hash', $this->input('ticket_hash', ''))
            ->active()
            ->exists();
    }

    public function rules()
    {
        return [
            'ticket_hash' => 'required',
            'message' => 'required',
            'with' => 'array',
            'with.*' => 'in:user.profile',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('tickets.on_create_message_error');
    }
}
