<?php
declare(strict_types=1);

namespace App\Http\Requests\Landing;

use App\Http\Requests\RealpathRule;
use App\Models\Landing;
use App\Http\Requests\Request;
use App\Models\Transit;
use Illuminate\Contracts\Validation\Validator;

class CreateRequest extends Request
{
    public function rules(): array
    {
        return array_merge(Landing::$rules, [
            'thumb_path' => 'string|max:255',
            'url' => 'required_if:is_external,1|url|max:255',
        ]);
    }

    public function messages(): array
    {
        return [
            'thumb_path.required' => trans('messages.thumb_path.required')
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('landings.on_create_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('is_external')) {
                $this->merge(['realpath' => '']);
                return;
            }

            // Check subdomain unique for `landings` table
            $exists_landing_subdomain = Landing::where('subdomain', $this->subdomain)->exists();
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

            (new RealpathRule())->validate($this, $validator);
        });
    }
}
