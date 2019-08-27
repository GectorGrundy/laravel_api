<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

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
            'id' => 'required|exists:offers,id,deleted_at,NULL'
        ];
    }

    public function messages()
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offers.on_delete_error');
    }
}
