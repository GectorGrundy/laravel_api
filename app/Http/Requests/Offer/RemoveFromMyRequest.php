<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Http\Requests\Request;

class RemoveFromMyRequest extends Request
{
    public function rules()
    {
        return [
            'offer_hash' => 'required|exists:offers,hash',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offers.on_remove_from_my_error');
    }

    public function moreValidation($validator)
    {
        $validator->after(function ($validator) {

            $offer_id = \Hashids::decode($this->get('offer_hash'))[0];

            $exists_offer_in_my_list = \DB::table('my_offers')
                ->where('offer_id', $offer_id)
                ->where('publisher_id', \Auth::user()->id)
                ->exists();

            if (!$exists_offer_in_my_list) {
                $validator->errors()->add('offer_id', trans('offers.not_exists_in_my_list_error'));
            }
        });
    }
}
