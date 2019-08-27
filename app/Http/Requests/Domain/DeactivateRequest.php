<?php
declare(strict_types=1);

namespace App\Http\Requests\Domain;

use App\Http\Requests\Request;
use App\Models\Domain;
use Illuminate\Contracts\Validation\Validator;

class DeactivateRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|numeric',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('domains.on_deactivate_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {

            $domain = Domain::active()->find($this->get('id'));

            if (\is_null($domain)) {
                return $validator->errors()->add('id', trans('validation.exists', [
                    'attribute' => 'id',
                ]));
            }

            if (!\in_array($domain->entity_type, [Domain::TDS_ENTITY_TYPE, Domain::REDIRECT_ENTITY_TYPE])) {
                return $validator->errors()->add('id', trans('validation.in', [
                    'attribute' => 'entity_type',
                ]));
            }

            if ($this->domainIsLast($domain)) {
                return $validator->errors()->add('id', trans('domains.is_last'));
            }
        });
    }

    private function domainIsLast(Domain $domain)
    {
        return !Domain::entityTypes([$domain->entity_type])
            ->where('id', '!=', $domain['id'])
            ->active()
            ->exists();
    }
}
