<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetTemplate;

use App\Http\Requests\Request;
use App\Models\TargetTemplate;

class EditRequest extends Request
{
    public function rules()
    {
        return array_merge(TargetTemplate::$rules, [
            'id' => 'required|exists:target_templates,id',
            'title_en' => 'required|string|max:255',
        ]);
    }

    protected function getFailedValidationMessage()
    {
        return trans('target_templates.on_edit_error');
    }
}
