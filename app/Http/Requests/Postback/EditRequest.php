<?php
declare(strict_types=1);

namespace App\Http\Requests\Postback;

use App\Http\Requests\Request;

use Hashids;

class EditRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Hashids::decode($this->postback_hash)[1] != auth()->id()) {
            return false;
        }

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
            'postback_hash' => 'required|exists:postbacks,hash',
            'url' => 'required|url',
	        'on_lead_add' => 'required|in:0,1',
	        'on_lead_approve' => 'required|in:0,1',
	        'on_lead_cancel' => 'required|in:0,1',
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

        ];
    }

    /**
     * Get message on validation error
     *
     */
    protected function getFailedValidationMessage()
    {
        return trans('postbacks.on_edit_error');
    }
}
