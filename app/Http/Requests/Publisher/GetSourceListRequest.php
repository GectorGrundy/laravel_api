<?php
declare(strict_types=1);

namespace App\Http\Requests\Publisher;

use App\Http\Requests\Request;

class GetSourceListRequest extends Request
{
    public function rules()
    {
        return [
            'type' => 'required|in:data1,data2,data3,data4'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('publishers.on_get_source_list_error');
    }
}
