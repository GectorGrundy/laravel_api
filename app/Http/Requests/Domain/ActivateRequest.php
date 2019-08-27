<?php
declare(strict_types=1);

namespace App\Http\Requests\Domain;

use App\Http\Requests\Request;

class ActivateRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:domains,id,is_active,0',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('domains.on_activate_error');
    }
}
