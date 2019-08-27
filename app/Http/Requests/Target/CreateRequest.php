<?php
declare(strict_types=1);

namespace App\Http\Requests\Target;

use App\Http\Requests\Request;
use App\Models\Target;

class CreateRequest extends Request
{
    public function rules()
    {
        return array_merge(Target::$rules, [
            'landing_type' => 'required|string|in:' . Target::EXTERNAL_LANDING . ',' . Target::INTERNAL_LANDING,
        ]);
    }

    protected function getFailedValidationMessage()
    {
        return trans('targets.on_create_error');
    }
}
