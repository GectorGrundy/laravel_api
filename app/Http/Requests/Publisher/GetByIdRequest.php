<?php
declare(strict_types=1);

namespace App\Http\Requests\Publisher;

use App\Http\Requests\Request;

class GetByIdRequest extends Request
{
    public function rules()
    {
        return [
            'news_id' => 'required|exists:news,id'
        ];
    }

    public function messages()
    {
        return [
            'news_id.required' => trans('news.news_id.required'),
            'news_id.exists' => trans('news.news_id.exists')
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('news.on_get_error');
    }
}
