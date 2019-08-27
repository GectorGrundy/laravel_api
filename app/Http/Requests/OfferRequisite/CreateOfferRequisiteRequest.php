<?php
declare(strict_types=1);

namespace App\Http\Requests\OfferRequisite;

use App\Http\Requests\Request;

class CreateOfferRequisiteRequest extends Request
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'offer_id' => 'required|exists:offers,id',
			'locale_id' => 'required|exists:locales,id',
			'content' => 'required|string'
		];
	}

	public function messages()
	{
		return [];
	}

	protected function getFailedValidationMessage()
	{
		return trans('offers.on_create_offer_requisite_error');
	}
}
