<?php
declare(strict_types=1);

namespace App\Http\Requests\Ticket;

use App\Http\Requests\Request;

class CreateRequest extends Request
{
    public function rules(): array
    {

        $rules = [
            'title' => 'required|string|max:255',
            'first_message' => 'required|string',
        ];

        if (\Auth::user()->isPublisher()) {
            $rules['with'] = 'array';
            $rules['with.*'] = 'in:last_message_user.profile';
        }

        return $rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('tickets.on_create_error');
    }
}
