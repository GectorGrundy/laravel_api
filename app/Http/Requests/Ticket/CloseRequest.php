<?php
declare(strict_types=1);

namespace App\Http\Requests\Ticket;

use App\Http\Requests\Request;
use App\Models\Ticket;

class CloseRequest extends Request
{
    public function authorize()
    {
        return Ticket::where('hash', $this->input('hash'))->active()->exists();
    }

    protected function getFailedValidationMessage()
    {
        return trans('tickets.on_close_error');
    }
}
