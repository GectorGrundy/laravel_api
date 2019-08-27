<?php
declare(strict_types=1);

namespace App\Http\Requests\News;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'date_from' => 'date_format:Y-m-d',
            'date_to' => 'date_format:Y-m-d',
            'offer_hashes' => 'array',
            'offer_hashes.*' => 'string',
            'per_page' => 'numeric|min:0|max:100',
            'page' => 'numeric|min:0',
            'with' => 'array',
            'with.*' => 'in:offer',
            'only_my' => 'in:0,1',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('news.on_get_list_error');
    }
}
