<?php
declare(strict_types=1);

namespace App\Http\Requests\News;

use App\Http\Requests\Request;
use App\Models\News;

class CreateRequest extends Request
{
    public function rules()
    {
        return News::$rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('news.on_create_error');
    }
}
