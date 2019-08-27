<?php
declare(strict_types=1);

namespace App\Http\Requests\Postback;

use App\Http\Requests\Request;
use Hashids;
use Auth;

class DeleteRequest extends Request
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		$publisher_id = 0;

		// If requested postback hash has пще encoded publisher_id
		if (isset(Hashids::decode($this->input('postback_hash'))[1])) {

			$publisher_id = Hashids::decode($this->input('postback_hash'))[1];
	    }

		return $publisher_id == Auth::user()->id;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'postback_hash' => 'required|exists:postbacks,hash'
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
		return trans('postbacks.on_delete_error');
	}
}
