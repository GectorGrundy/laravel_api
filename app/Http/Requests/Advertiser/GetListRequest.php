<?php
declare(strict_types=1);

namespace App\Http\Requests\Advertiser;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'hashes' => 'array|max:200',
            'hashes.*' => 'string',
            'page' => 'required|min:0|numeric',
            'per_page' => 'required|numeric|min:0|max:200',
            'search_field' => 'in:email',
            'search' => 'string',
            'with' => 'array',
            'with.*' => 'in:profile,accounts',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_get_list_error');
    }
}
