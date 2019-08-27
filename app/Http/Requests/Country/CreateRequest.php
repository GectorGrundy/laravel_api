<?php
declare(strict_types=1);

namespace App\Http\Requests\Country;

use App\Http\Requests\Request;

class CreateRequest extends Request
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
            'title' => 'required|unique:countries,title|max:255',
            'code' => 'required|unique:countries,code|size:2',
            'thumb_path' => 'required|max:255',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'code.unique' => trans('countries.code.unique')
        ];
    }

    /**
     * Get message on validation error
     *
     */
    protected function getFailedValidationMessage()
    {
        return trans('countries.on_create_error');
    }
}
