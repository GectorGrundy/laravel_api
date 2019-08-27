<?php
declare(strict_types=1);

namespace App\Http\Requests\Country;

use App\Http\Requests\Request;

class EditRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'country_id' => 'required|exists:countries,id',
            'title' => 'required',
            'code' => 'required|size:2',
            'thumb_path'=> 'required|max:255',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Get message on validation error
     *
     */
    protected function getFailedValidationMessage()
    {
        return trans('countries.on_edit_error');
    }
}
