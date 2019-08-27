<?php
declare(strict_types=1);

namespace App\Http\Requests\Ticket;

use App\Models\Ticket;
use App\Http\Requests\Request;

class MarkAsRequest extends Request
{
    public function authorize()
    {
        // Show validation error if hash wasnt send
        if (!$this->filled('hash')) {
            return true;
        }

        return Ticket::where('hash', $this->input('hash'))->exists();
    }

    public function rules(): array
    {
        return [
            'hash' => 'required|string'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('tickets.on_mark_as_read_error');
    }
}
