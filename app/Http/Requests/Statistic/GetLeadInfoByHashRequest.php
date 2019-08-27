<?php
declare(strict_types=1);

namespace App\Http\Requests\Statistic;

use App\Http\Requests\Request;
use Hashids;

class GetLeadInfoByHashRequest extends Request
{
	public function rules()
	{
		return [
			'hash' => 'required|string',
		];
	}

	public function moreValidation($validator)
	{
		$validator->after(function ($validator) {
			$lead_decoded_data = Hashids::decode($this->get('hash'));
			if (!isset($lead_decoded_data[0])) {
				$validator->errors()->add('hash', trans('statistics.exists.lead_hash'));
				return false;
			}

			$this->merge([
				'id' => $lead_decoded_data[0]
			]);
		});
	}

	protected function getFailedValidationMessage()
	{
		return trans('statistics.on_get_lead_info_error');
	}
}
