<?php
declare(strict_types=1);

namespace App\Http\Requests\Domain;

use Hashids;
use App\Models\{
    Domain, User
};
use App\Http\Requests\Request;
use Illuminate\Contracts\Validation\Validator;

class EditRequest extends Request
{
    public function authorize()
    {
        $domain_id = Hashids::decode($this->domain_hash)[0];

        $this->merge(['domain_id' => $domain_id]);

        switch (\Auth::user()['role']) {
            case User::ADMINISTRATOR:
                return true;

            case User::PUBLISHER:
                return Domain::where('user_id', \Auth::id())->where('id', $domain_id)->exists();
        }
    }

    public function rules(): array
    {
        return [
            'domain_hash' => 'required|exists:domains,hash',
            'domain' => 'required|unique:domains,domain,' . $this->domain_hash . ',hash,deleted_at,NULL',
            'type' => 'required|in:' . Domain::CUSTOM_TYPE . ',' . Domain::PARKED_TYPE,
            'entity_type' => 'required|in:'
                . ',' . Domain::TRANSIT_ENTITY_TYPE
                . ',' . Domain::LANDING_ENTITY_TYPE
                . ',' . Domain::FLOW_ENTITY_TYPE,
            'donor_url' => 'active_url',
            'is_public' => 'in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'domain.unique' => trans('domains.domain.unique'),
            'fallback_flow_hash.exists' => trans('domains.flow.incorrect'),
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('domains.on_edit_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            (new DomainExtraValidator())->validate($this, $validator, 'edit');
        });
    }
}
