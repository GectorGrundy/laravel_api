<?php
declare(strict_types=1);

namespace App\Strategies\OfferListing;

use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\TargetGeo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PublisherOfferList implements OfferListingStrategy
{

    public function get(Request $request, Builder $offers)
    {
        $user = \Auth::user();

        $with = [
            'targets' => function ($builder) use ($user) {
                $builder->availableForUser($user);
            },
            'targets.template',
            'targets.locale',
            'targets.target_geo',
            'targets.target_geo.country',
            'targets.target_geo.payout_currency',
            'targets.target_geo.price_currency',
            'offer_categories',
            'labels'
        ];

        /**
         * @var Collection $offers
         */
        $offers = $offers->excludeArchived()->with($with)->get();

        $target_geo = new TargetGeo();
        foreach ($offers as &$offer) {
            foreach ($offer->targets as &$target) {
                $target->target_geo = $target_geo->replaceCustomStakes(
                    $target->target_geo, $user->id
                );
            }
        }

        $page = $request->get('page', 1);
        $per_page = $request->get('per_page', 20);
        $offset = paginationOffset($page, $per_page);

        $offers = Offer::rejectInactiveOffersForPublisher($offers);

        $total_offers = $offers->count();

        $offers = \array_slice($offers->toArray(), $offset, $per_page);

        return new LengthAwarePaginator($offers, $total_offers, $per_page, $page);
    }
}
