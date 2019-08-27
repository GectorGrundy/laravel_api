<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Offer;
use Dingo\Api\Routing\Helpers;
use App\Models\OfferSource;
use Illuminate\Database\Eloquent\Builder;

class OfferSourceController extends Controller
{
    use Helpers;

    public function getList()
    {
        $offer_sources = OfferSource::orderBy('id')->get();

        return ['response' => $offer_sources, 'status_code' => 200];
    }

    public function getListForOfferFilter()
    {
        $user = \Auth::user();

        $result = OfferSource::all()
            ->map(function (OfferSource $offer_source) use ($user) {

                $offers = Offer::whereHas('offer_sources', function (Builder $builder) use ($offer_source) {
                    return $builder->where('offer_source_id', $offer_source['id']);
                })->get();

                if ($user->isPublisher()) {
                    $offers = Offer::rejectInactiveOffersForPublisher($offers);
                }

                $offer_source['offers_count'] = $offers->count();
                return $offer_source;
            });

        return [
            'response' => $result,
            'status_code' => 200
        ];
    }
}
