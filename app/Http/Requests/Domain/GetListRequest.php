<?php
declare(strict_types=1);

namespace App\Http\Requests\Domain;

use App\Exceptions\User\UnknownUserRole;
use App\Http\Requests\Request;
use App\Models\Domain;
use Illuminate\Contracts\Validation\Validator;

class GetListRequest extends Request
{
    public function rules(): array
    {
        return [
            'with_public' => 'in:0,1',
            'with' => 'array',
            'with.*' => 'in:entity,entity.offer,entity.locale,flow,paths,replacements',
            'entity_types' => 'array',
            'entity_types.*' => 'in:' . implode(',', [
                    Domain::LANDING_ENTITY_TYPE,
                    Domain::TRANSIT_ENTITY_TYPE,
                    Domain::FLOW_ENTITY_TYPE,
                    Domain::TDS_ENTITY_TYPE,
                    Domain::REDIRECT_ENTITY_TYPE,
                ]),
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {
            $user = \Auth::user();

            // Проверка прав доступа пользователя к потоку. Админу выдны все, паблишеру только свои
            if ($user->isAdmin()) {
                $rules = ['flow_hash' => 'sometimes|string|exists:flows,hash'];

            } else if ($user->isPublisher()) {
                $rules = ['flow_hash' => 'sometimes|string|exists:flows,hash,publisher_id,' . $user['id']];

            } else {
                throw new UnknownUserRole($user['role']);
            }

            if (\Validator::make($this->all(), $rules)->fails()) {
                $validator->errors()->add('flow_hash', trans('validation.in', [
                    'attribute' => 'flow_hash',
                ]));
            }
        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('domains.on_get_list_error');
    }
}
