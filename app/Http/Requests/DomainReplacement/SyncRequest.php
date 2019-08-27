<?php
declare(strict_types=1);

namespace App\Http\Requests\DomainReplacement;

use App\Http\Requests\Request;
use Illuminate\Contracts\Validation\Validator;

class SyncRequest extends Request
{
    public function rules()
    {
        return [
            'domain_hash' => 'required|exists:domains,hash,user_id,' . \Auth::id(),
            'replacements' => 'present|array',
            'replacements.*' => 'array|size:2',
            'replacements.*.from' => 'present|string',
            'replacements.*.to' => 'present|string',
        ];
    }

    public function moreValidation(Validator $validator)
    {

        $validator->after(function (Validator $validator) {
            foreach ($this['replacements'] as $replacement) {
                if (strlen($replacement['from']) < 1) {
                    $validator->errors()->add('from', trans('domain_replacements.on_from_size_error'));
                }
            }
        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('domain_replacements.on_sync_error');
    }
}
