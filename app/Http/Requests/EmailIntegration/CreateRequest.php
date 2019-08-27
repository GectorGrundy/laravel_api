<?php
declare(strict_types=1);

namespace App\Http\Requests\EmailIntegration;

use App\Http\Requests\Request;
use App\Models\EmailIntegration;

class CreateRequest extends Request
{
    public function rules()
    {
        return EmailIntegration::$rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('integrations.on_create_error');
    }
}
