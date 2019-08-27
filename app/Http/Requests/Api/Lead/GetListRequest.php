<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\Lead;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'date_start' => 'required|numeric',
            'date_end' => 'required|numeric',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('leads.on_get_list_error');
    }
}
