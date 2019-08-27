<?php
declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class PromoQuestionRequest extends Request
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'message' => 'required|string|max:255',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('api.on_error');
    }
}
