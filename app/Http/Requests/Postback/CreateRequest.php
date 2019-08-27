<?php
declare(strict_types=1);

namespace App\Http\Requests\Postback;

use App\Http\Requests\Request;
use Auth;

use App\Models\Flow;

class CreateRequest extends Request
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		// If it's flow's postback
		if ($this->flow_hash != 0) {
			return Flow::where('hash', $this->flow_hash)
				->where('publisher_id', Auth::id())->exists();
		}

		return true;
	}

	public function rules()
	{
		$rules = [
			'url' => 'required|url',
			'on_lead_add' => 'required|in:0,1',
			'on_lead_approve' => 'required|in:0,1',
			'on_lead_cancel' => 'required|in:0,1',
			'flow_hash' => 'required',
		];

		// If flow_hash == 0 - it's a global postback
		if ($this->flow_hash != 0) {
			$rules['flow_hash'] = 'required|exists:flows,hash';
		}

		return $rules;
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
		return trans('postbacks.on_create_error');
	}

	/**
	 * Get message on authorization error
	 *
	 */
	protected function getFailedAuthorizationMessage()
	{
		return trans('postbacks.forbidden_error');
	}
}
