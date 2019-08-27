<?php
declare(strict_types=1);

namespace App\Http\Requests\Domain;

use App\Http\Requests\Request;
use App\Models\Domain;
use Illuminate\Contracts\Validation\Validator;

class CreateRequest extends Request
{
    public function rules(): array
    {
        return [
            'domain' => 'required|string',
            'type' => 'required|in:' . Domain::CUSTOM_TYPE . ',' . Domain::PARKED_TYPE . ',' . Domain::SYSTEM_TYPE,
            'entity_type' => 'required|in:'
                . Domain::TDS_ENTITY_TYPE
                . ',' . Domain::TRANSIT_ENTITY_TYPE
                . ',' . Domain::LANDING_ENTITY_TYPE
                . ',' . Domain::FLOW_ENTITY_TYPE
                . ',' . Domain::REDIRECT_ENTITY_TYPE,
            'donor_url' => 'active_url',
            'is_public' => 'in:0,1',
            'is_active' => 'in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'fallback_flow_hash.exists' => trans('domains.flow.incorrect'),
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('domains.on_create_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            (new DomainExtraValidator())->validate($this, $validator);
        });
    }
}
