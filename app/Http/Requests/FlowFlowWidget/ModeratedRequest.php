<?php
declare(strict_types=1);

namespace App\Http\Requests\FlowFlowWidget;

use App\Http\Requests\Request;

class ModeratedRequest extends Request
{
    public function rules(): array
    {
        return [
            'hash' => 'required|exists:flow_flow_widget,hash,is_moderated,0',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('flow_widget.on_moderate_error');
    }
}
