<?php
declare(strict_types=1);

namespace App\Http\Requests\FlowFlowWidget;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hash' => 'required|exists:flow_flow_widget,hash',
        ];
    }

    public function messages(): array
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('flow_widget.on_delete_error');
    }

    public function moreValidation($validator)
    {
        $validator->after(function ($validator) {
            $flow_flow_widget_id = \Hashids::decode($this->hash)[0];
            $this->merge(['flow_flow_widget_id' => $flow_flow_widget_id]);
        });
    }
}
