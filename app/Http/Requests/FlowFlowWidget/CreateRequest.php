<?php
declare(strict_types=1);

namespace App\Http\Requests\FlowFlowWidget;

use App\Http\Requests\Request;
use App\Models\{
    Flow, FlowWidget
};

class CreateRequest extends Request
{
    public function rules(): array
    {
        return [
            'flow_hash' => 'required|exists:flows,hash,publisher_id,' . \Auth::id(),
            'flow_widget_id' => 'required|exists:flow_widgets,id',
            'attributes' => 'required|array'
        ];
    }

    public function messages(): array
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('flow_widget.on_create_error');
    }

    public function moreValidation($validator)
    {
        $validator->after(function ($validator) {

            $this->merge(['flow_id' => (new Flow())->getIdFromHash($this->flow_hash)]);

            $flow_widget = FlowWidget::find($this->get('flow_widget_id'));

            $schema_validator = \Validator::make($this->get('attributes', []), $flow_widget->schema_array);
            if ($schema_validator->fails()) {

                foreach ($schema_validator->errors()->getMessages() as $error_field => $error) {
                    $validator->errors()->add($error_field, $error);
                }
            }
        });
    }
}
