<?php
declare(strict_types=1);

namespace App\Http\Requests\Flow;

use App\Models\Flow;
use App\Http\Requests\Request;
use App\Exceptions\Hashids\NotDecodedHashException;
use App\Models\Scopes\HideDraftStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetByHashRequest extends Request
{
    public function rules()
    {
        return [
            'with_draft' => 'in:1',
            'with.*' => 'in:' . implode(',', [
                    'target',
                    'target.template',
                    'fallback_target',
                    'extra_flow',
                    'landings',
                    'landings.target',
                    'landings.locale',
                    'transits',
                    'transits.target',
                    'transits.locale',
                    'offer.currency',
                    'widgets.widget',
                    'cloak.cloak_system',
                    'group',
                    'offer',
                    'day_statistics',
                ])
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {
            try {
                $id = (new Flow())->getIdFromHash($this->input('flow_hash'));

                if ($this->input('with_draft', false)) {
                    Flow::withoutGlobalScope(HideDraftStatus::class)->findOrFail($id);
                } else {
                    Flow::findOrFail($id);
                }
                $this->merge(['id' => $id]);

            } catch (NotDecodedHashException | ModelNotFoundException $e) {
                $validator->errors()->add('flow_hash', trans('validation.exists', [
                    'attribute' => 'flow_hash'
                ]));
            }

        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('flows.on_get_error');
    }
}
