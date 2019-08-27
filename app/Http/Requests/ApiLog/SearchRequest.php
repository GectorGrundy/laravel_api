<?php
declare(strict_types=1);

namespace App\Http\Requests\ApiLog;

use App\Http\Requests\Request;

class SearchRequest extends Request
{
    public function rules()
    {
        return [
            'search' => 'required|string',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('api_logs.on_search_error');
    }
}
