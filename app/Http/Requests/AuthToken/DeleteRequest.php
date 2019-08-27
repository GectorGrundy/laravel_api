<?php
declare(strict_types=1);

namespace App\Http\Requests\AuthToken;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules(): array
    {
        return [
            'hash' => [
                'required',
                'string',
                'exists:auth_tokens,hash,user_id,' . $this->input('auth_token')['user_id'],
                'not_in:' . $this->input('auth_token')['hash']
            ]
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('auth_token.on_delete_error');
    }
}
