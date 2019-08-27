<?php
declare(strict_types=1);

namespace App\Http\Requests\Postback;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'flow_hash'=> 'exists:flows,hash'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('postbacks.on_get_list_error');
    }
}
