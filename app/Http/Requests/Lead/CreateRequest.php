<?php
declare(strict_types=1);

namespace App\Http\Requests\Lead;

use App\Http\Requests\Request;
use Illuminate\Contracts\Validation\Validator;

class CreateRequest extends Request
{
    public function rules()
    {
        return [
            'flow_id' => 'required|numeric|exists:flows,id,deleted_at,NULL',
            'target_geo_id' => 'required|numeric|exists:target_geo,id',
            'contacts' => 'required|string',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('leads.on_create_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validation) {
            $contacts = explode("\n", $this->input('contacts'));

            foreach ($contacts as $contact) {
                $data = explode(',', $contacts[0]);

                if (!isset($data[0], $data[1])) {
                    $validation->errors()->add('contacts', trans('leads.contacts.incorrect'));
                }
            }
        });
    }
}
