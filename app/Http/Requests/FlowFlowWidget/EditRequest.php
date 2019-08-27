<?php
declare(strict_types=1);

namespace App\Http\Requests\FlowFlowWidget;

use App\Http\Requests\Request;
use App\Models\FlowFlowWidget;

class EditRequest extends Request
{
    public function rules(): array
    {
        return [
            'hash' => 'required|exists:flow_flow_widget,hash',
            'attributes' => 'required|array'
        ];
    }

    public function messages(): array
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('flow_widget.on_edit_error');
    }

    public function moreValidation($validator)
    {
        $validator->after(function ($validator) {
            $flow_flow_widget_id = \Hashids::decode($this->hash)[0];
            $this->merge(['flow_flow_widget_id' => $flow_flow_widget_id]);

            $flow_flow_widget = FlowFlowWidget::find($flow_flow_widget_id);

            $schema_validator = \Validator::make($this->get('attributes', []), $flow_flow_widget->widget->schema_array);
            if ($schema_validator->fails()) {

                foreach ($schema_validator->errors()->getMessages() as $error_field => $error) {
                    $validator->errors()->add($error_field, $error);
                }
            }
        });
    }
}
