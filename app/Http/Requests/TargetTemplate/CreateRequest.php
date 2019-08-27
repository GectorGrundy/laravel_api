<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetTemplate;

use App\Http\Requests\Request;
use App\Models\TargetTemplate;

class CreateRequest extends Request
{
    public function rules()
    {
        return array_merge(TargetTemplate::$rules, [
            'title_en' => 'required|string|max:255',
        ]);
    }

    protected function getFailedValidationMessage()
    {
        return trans('target_templates.on_create_error');
    }
}
