<?php
declare(strict_types=1);

namespace App\Http\Requests\PostbackOut;

use App\Http\Requests\Request;


class GetListRequest extends Request
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
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
		$rules = [

		];

		// If flow_hash == 0 - it's a global postback
		if ($this->flow_hash != 0) {

			$rules['flow_hashes'] = 'array';
			$rules['flow_hashes.*'] ='exists:flows,hash';
		}

		return $rules;
	}

	/**
	 * Extra validation
	 *
	 * @param $validator
	 */
	public function moreValidation($validator)
	{

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
		return trans('postbackout.on_get_list_error');
	}
}
