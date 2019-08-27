<?php
declare(strict_types=1);

namespace App\Http\Requests\Lead;

use App\Exceptions\Hashids\NotDecodedHashException;
use App\Http\Requests\Request;
use App\Models\Lead;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetByHashRequest extends Request
{
    public function rules(): array
    {
        $user = \Auth::user();

        $admin_with = 'postbackin_logs,status_log.integration,status_log';
        $publisher_with = 'postbackin_logs,status_log,status_log';

        return [
            'hash' => 'required|string',
            'with' => 'array',
            'with.*' => 'in:' . ($user->isAdmin() ? $admin_with : $publisher_with),
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {
            try {
                $id = (new Lead())->getIdFromHash($this->hash);
                $lead = Lead::findOrFail($id);
            } catch (NotDecodedHashException | ModelNotFoundException $e) {
                return $validator->errors()->add('hash', trans('validation.exists', [
                    'attribute' => 'hash'
                ]));
            }

            $this->merge([
                'lead' => $lead
            ]);
        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('leads.on_get_info_error');
    }
}
