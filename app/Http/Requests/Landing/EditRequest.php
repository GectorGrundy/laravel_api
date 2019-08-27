<?php
declare(strict_types=1);

namespace App\Http\Requests\Landing;

use App\Http\Requests\RealpathRule;
use App\Models\Flow;
use App\Models\Landing;
use App\Http\Requests\Request;
use App\Models\Transit;
use Illuminate\Contracts\Validation\Validator;

class EditRequest extends Request
{
    public function rules()
    {
        return array_merge(Landing::$rules, [
            'id' => 'required|exists:landings,id',
            'thumb_path' => 'string|max:255',
            'url' => 'required_if:is_external,1|url|max:255',
        ]);
    }

    protected function getFailedValidationMessage()
    {
        return $this->error_message ?: trans('offers.on_edit_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('is_external')) {
                $this->merge(['realpath' => '']);
                return;
            }

            // Check subdomain unique for `landings` table
            $exists_landing_subdomain = Landing::where('subdomain', $this->subdomain)
                ->where('id', '!=', $this->id)
                ->exists();
            if ($exists_landing_subdomain) {
                $validator->errors()->add('subdomain', trans('landings.subdomain.unique'));
                return;
            }

            // Check subdomain unique for `transits` table
            $exists_transit_subdomain = Transit::where('subdomain', $this->subdomain)->exists();
            if ($exists_transit_subdomain) {
                $validator->errors()->add('subdomain', trans('landings.subdomain.unique'));
                return;
            }

            if ($this->input('is_private') != 0) {

                $flows = Flow::leftJoin('flow_landing', 'flows.id', '=', 'flow_landing.flow_id')
                    ->where('status', 'active')
                    ->where('landing_id', $this->input('id'))
                    ->get();

                if ($flows->count()) {

                    $this->error_message = trans('landings.flow.exists');
                    addAccessErrorToValidator($validator, $flows);
                }
            }

            (new RealpathRule())->validate($this, $validator);
        });
    }
}
