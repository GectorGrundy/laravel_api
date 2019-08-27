<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Http\Requests\Request;
use Hashids;
use DB;
use Auth;

class AddToMyRequest extends Request
{
	public function rules()
	{
		return [
			'offer_hash' => 'required|exists:offers,hash',
		];
	}

	protected function getFailedValidationMessage()
	{
		return trans('offers.on_add_to_my_error');
	}

	public function moreValidation($validator)
	{
		$validator->after(function ($validator) {
			if (!$validator->errors()->has('offer_hash')) {

				$offer_id = Hashids::decode($this->get('offer_hash'))[0];

				$exists_offer_in_my_list = DB::table('my_offers')
					->where('offer_id', $offer_id)
					->where('publisher_id', Auth::user()->id)
					->exists();

				if ($exists_offer_in_my_list) {
					$validator->errors()->add('offer_id', trans('offers.unique_my_list_error'));
				}
			}

		});
	}
}
