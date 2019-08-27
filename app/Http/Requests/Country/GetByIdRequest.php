<?php
declare(strict_types=1);

namespace App\Http\Requests\Country;

use App\Http\Requests\Request;

class GetByIdRequest extends Request
{
    public function rules()
    {
        return [
            'country_id' => 'required|exists:countries,id'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('countries.on_get_error');
    }
}
