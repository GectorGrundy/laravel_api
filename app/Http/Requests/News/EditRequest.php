<?php
declare(strict_types=1);

namespace App\Http\Requests\News;

use App\Models\News;
use App\Http\Requests\Request;

class EditRequest extends Request
{
    public function rules()
    {
        return array_merge(News::$rules, [
            'id' => 'required|exists:news,id,deleted_at,NULL',
        ]);
    }

    protected function getFailedValidationMessage()
    {
        return trans('news.on_edit_error');
    }
}
