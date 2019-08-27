<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Http\Requests\OfferRequisite as R;
use App\Models\OfferRequisiteTranslation;
use App\Models\Offer;

class OfferRequisiteController extends Controller
{
	use Helpers;

	/**
	 * Create new offer requisite
	 *
	 * @param R\CreateOfferRequisiteRequest $request
	 * @return mixed
	 */
	public function create(R\CreateOfferRequisiteRequest $request)
	{
		$offer_requisite = OfferRequisiteTranslation::create($request->all());

		$offer_requisite = OfferRequisiteTranslation::with(['locale'])->find($offer_requisite->id);

		return $this->response->accepted(null, [
			'message' => trans('offer_requisites.on_create_offer_requisite_success'),
			'response' => $offer_requisite,
			'status_code' => 202
		]);
	}

	/**
	 * Edit offer requisite data
	 *
	 * @param R\EditOfferRequisiteRequest $request
	 * @return mixed
	 */
	public function edit(R\EditOfferRequisiteRequest $request)
	{
		OfferRequisiteTranslation::find($request->get('id'))->update($request->all());

		$offer_requisite = OfferRequisiteTranslation::with(['locale'])->find($request->get('id'));

		return $this->response->accepted(null, [
			'message' => trans('offer_requisites.on_edit_offer_requisite_success'),
			'response' => $offer_requisite,
			'status_code' => 202
		]);
	}

	/**
	 * Delete offer requisite
	 *
	 * @param R\DeleteOfferRequisiteRequest $request
	 * @return mixed
	 */
	public function delete(R\DeleteOfferRequisiteRequest $request)
	{
		OfferRequisiteTranslation::find($request->id)->delete();

		return $this->response->accepted(null, [
			'message' => trans('offer_requisites.on_delete_offer_requisite_success'),
			'response' => [],
			'status_code' => 202
		]);
	}
}
