<?php
declare(strict_types=1);

namespace App\Http\Requests\News;

use App\Http\Requests\Request;

class GetByHashRequest extends Request
{
    public function rules()
    {
        return [
            'hash' => 'required|string',
            'with' => 'array',
            'with.*' => 'in:offer',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('news.on_get_error');
    }
}
