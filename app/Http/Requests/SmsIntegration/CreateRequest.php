<?php
declare(strict_types=1);

namespace App\Http\Requests\SmsIntegration;

use App\Http\Requests\Request;
use App\Models\SmsIntegration;

class CreateRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return SmsIntegration::$rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('integrations.on_create_error');
    }
}
