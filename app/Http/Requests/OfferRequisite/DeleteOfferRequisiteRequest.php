<?php
declare(strict_types=1);

namespace App\Http\Requests\OfferRequisite;

use App\Http\Requests\Request;

class DeleteOfferRequisiteRequest extends Request
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'id' => 'required|exists:offer_requisite_translation,id'
		];
	}

	public function messages()
	{
		return [];
	}

	protected function getFailedValidationMessage()
	{
		return trans('offers.on_delete_offer_requisite_error');
	}
}
