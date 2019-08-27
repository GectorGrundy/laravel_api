<?php
declare(strict_types=1);

namespace App\Http\Requests\Integration;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:integrations,id,deleted_at,NULL'
        ];
    }

    public function messages()
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('integrations.on_delete_error');
    }
}
