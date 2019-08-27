<?php

namespace App\Strategies\OfferListing;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class AdminOfferList implements OfferListingStrategy
{

    public function get(Request $request, Builder $offers)
    {
        $with = [
            'targets.template',
            'targets.locale',
            'targets.target_geo',
            'targets.target_geo.country', 'targets.target_geo.payout_currency',
            'targets.target_geo.price_currency',
            'targets.target_geo.rules',
            'offer_sources', 'landings', 'landings.locale', 'transits', 'transits.locale',
            'countries', 'offer_categories', 'labels', 'publishers', 'user_groups'
        ];

        /**
         * @var Collection $offers
         */
        $offers = $offers->with($with)->excludeArchived()->get();

        $page = $request->get('page', 1);
        $per_page = $request->get('per_page', 20);
        $offset = paginationOffset($page, $per_page);

        $total_offers = $offers->count();

        $offers = \array_slice($offers->toArray(), $offset, $per_page);

        return new LengthAwarePaginator($offers, $total_offers, $per_page, $page);
    }
}
