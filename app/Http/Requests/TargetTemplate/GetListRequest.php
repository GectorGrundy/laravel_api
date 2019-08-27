<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetTemplate;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'with' => 'array',
            'with.*' => 'in:translations'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('targets.on_get_list_error');
    }
}
