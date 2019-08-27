<?php
declare(strict_types=1);

namespace App\Http\Requests\OfferRequisite;

use App\Http\Requests\Request;

class EditOfferRequisiteRequest extends Request
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'id' => 'required|exists:offer_requisite_translation,id',
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
		return trans('offers.on_edit_offer_requisite_error');
	}
}
