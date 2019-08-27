<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\OfferLabel;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\OfferLabel as R;
use Illuminate\Database\Eloquent\Builder;

class OfferLabelController extends Controller
{
    use Helpers;

    /**
     * @api {GET} /offer_labels.getList offer_labels.getList
     * @apiGroup OfferLabel
     * @apiPermission admin
     * @apiPermission advertiser
     * @apiPermission publisher
     * @apiParam {String[]=offers_count} [with[]]
     * @apiSampleRequest /offer_labels.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $user = \Auth::user();

        $offer_labels = OfferLabel::all();

        if (\in_array('offers_count', $request->input('with', []))) {

            $offer_labels->map(function (OfferLabel $offer_label) use ($user) {

                $offers = Offer::active()
                    ->whereHas('labels', function (Builder $builder) use ($offer_label) {
                        return $builder->where('offer_label_id', $offer_label['id']);
                    });

                if ($user->isPublisher()) {
                    $offers = $offers->active()->get();
                    $offers = Offer::rejectInactiveOffersForPublisher($offers);

                } elseif ($user->isAdvertiser()) {
                    $offers = $offers->active()->get();
                } else {
                    $offers = $offers->whereIn('status', [Offer::ACTIVE, Offer::ARCHIVED])->get();
                }

                $offer_label['offers_count'] = $offers->count();
                return $offer_label;
            });
        }

        return ['response' => $offer_labels, 'status_code' => 200];
    }
}
