<?php
declare(strict_types=1);

namespace App\Http\Requests\SmsIntegration;

use App\Http\Requests\Request;
use App\Models\SmsIntegration;

class EditRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return array_merge(SmsIntegration::$rules, [
            'id' => 'required|numeric|exists:sms_integrations,id',
        ]);
    }

    protected function getFailedValidationMessage()
    {
        return trans('integrations.on_edit_error');
    }
}
