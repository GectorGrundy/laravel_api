<?php
declare(strict_types=1);

namespace App\Http\Requests\CloakDomainPath;

use App\Http\Requests\Request;
use App\Models\CloakDomainPath;
use App\Models\CloakSystem;
use Auth;
use Illuminate\Contracts\Validation\Validator;

class CreateRequest extends Request
{
    public function rules()
    {
        return [
            'flow_hash' => 'required|exists:flows,hash,publisher_id,' . Auth::id() . ',deleted_at,NULL',
            'status' => [
                'required',
                'in:' . implode(',', [
                    CloakDomainPath::SAFEPAGE_STATUS,
                    CloakDomainPath::MONEYPAGE_STATUS,
                    CloakDomainPath::MONEYPAGE_FOR_STATUS
                ])
            ],
            'cloak_system_id' => 'required|numeric|exists:cloak_systems,id',
            'is_cache_result' => 'required|in:0,1',
            'data_parameter' => [
                'required_if:status,' . CloakDomainPath::MONEYPAGE_FOR_STATUS,
                'in:' . implode(',', [
                    CloakDomainPath::DATA1,
                    CloakDomainPath::DATA2,
                    CloakDomainPath::DATA3,
                    CloakDomainPath::DATA4,
                ])
            ],
            'identifiers' => 'present|string'
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {
            (new PathValidator())->validate($this, $validator, 'create');

            $cloak_system = CloakSystem::find($this->cloak_system_id);

            $rules = $cloak_system->schema_array;
            if ($this->input('status') === CloakDomainPath::SAFEPAGE_STATUS) {
                $rules = array_map(function ($rule) {
                    return str_replace('required|', '', $rule);
                }, $rules);
            }
            $schema_validator = \Validator::make($this->get('attributes', []), $rules);

            if ($schema_validator->fails()) {

                foreach ($schema_validator->errors()->getMessages() as $error_field => $error) {
                    $validator->errors()->add($error_field, $error);
                }
            }
        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('cloak_domain_paths.on_create_error');
    }
}
