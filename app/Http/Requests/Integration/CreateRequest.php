<?php
declare(strict_types=1);

namespace App\Http\Requests\Integration;

use App\Http\Requests\Request;
use App\Models\Integration;
use Illuminate\Contracts\Validation\Validator;

class CreateRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return array_merge(Integration::$rules, [
            'worktime' => 'present|array',
        ]);
    }

    public function messages()
    {
        return [];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ((int)$this->get('is_works_all_time') === 1) {
                $this->merge(['worktime' => []]);
            }

            if (count($this->get('worktime', [])) < 1) {
                $this->merge(['is_works_all_time' => 1]);
            }
        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('integrations.on_create_error');
    }
}
