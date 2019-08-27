<?php
declare(strict_types=1);

namespace App\Http\Requests\EmailIntegration;

use App\Http\Requests\Request;
use App\Models\EmailIntegration;

class EditRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return array_merge(EmailIntegration::$rules, [
            'id' => 'required|numeric|exists:email_integrations,id',
        ]);
    }

    protected function getFailedValidationMessage()
    {
        return trans('integrations.on_edit_error');
    }
}
