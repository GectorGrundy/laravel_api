<?php
declare(strict_types=1);

namespace App\Http\Requests\EmailLog;

use App\Http\Requests\Request;

class ShowRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'entity_id' => 'required|numeric',
            'entity_type' => 'required|string|in:lead,preorder'
        ];
    }

    public function messages()
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('email_logs.on_show_error');
    }
}
