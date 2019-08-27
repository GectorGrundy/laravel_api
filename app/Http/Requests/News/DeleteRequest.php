<?php
declare(strict_types=1);

namespace App\Http\Requests\News;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:news,id,deleted_at,NULL'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('news.on_delete_error');
    }
}
