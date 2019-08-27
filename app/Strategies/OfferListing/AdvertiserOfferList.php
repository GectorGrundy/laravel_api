<?php
declare(strict_types=1);

namespace App\Strategies\OfferListing;

use App\Models\Target;
use App\Models\TargetGeo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdvertiserOfferList implements OfferListingStrategy
{

    public function get(Request $request, Builder $offers)
    {
        $offers_with = [
            'offer_sources',
            'landings',
            'landings.locale',
            'transits',
            'transits.locale',
            'countries',
            'offer_categories',
            'labels',
            'targets' => function ($builder) {
                $builder->whereHas('target_geo.rules');
            } ,
            'targets.template',
            'targets.locale',
            'targets.target_geo' => function ($builder) {
                $builder->whereHas('rules');
            },
            'targets.target_geo.rules',
            'targets.target_geo.country',
            'targets.target_geo.payout_currency',
            'targets.target_geo.price_currency',
        ];

        /**
         * @var Collection $offers
         */
        $offers = $offers->active()->with($offers_with)->get();

        $page = $request->get('page', 1);
        $per_page = $request->get('per_page', 20);
        $offset = paginationOffset($page, $per_page);

        $total_offers = $offers->count();

        $offers = \array_slice($offers->toArray(), $offset, $per_page);

        return new LengthAwarePaginator($offers, $total_offers, $per_page, $page);
    }
}
