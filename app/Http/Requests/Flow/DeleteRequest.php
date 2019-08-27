<?php
declare(strict_types=1);

namespace App\Http\Requests\Flow;

use Hashids;
use App\Models\Domain;
use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules(): array
    {
        return [
            'flow_hash' => 'required|exists:flows,hash,deleted_at,NULL,publisher_id,' . \Auth::id()
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('flows.on_delete_error');
    }

    public function moreValidation($validator)
    {
        $validator->after(function ($validator) {

            $flow_id = Hashids::decode($this->get('flow_hash'))[0];

            $relations = Domain::where('fallback_flow_id', $flow_id)->get();

            if (count($relations) > 0) {
                $validator->errors()->add('reason', trans('flows.has_domain_relation'));
                foreach ($relations AS $relation) {
                    $validator->errors()->add('domain', $relation->domain);
                }
            }
        });
    }
}
