<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\Lead;

use App\Http\Requests\Request;

class GetInfoRequest extends Request
{
    public function rules()
    {
        return [
            'hashes' => 'required|string'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('leads.on_get_info_error');
    }
}
