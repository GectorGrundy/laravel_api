<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\Event;
use App\Events\TargetGeo\TargetGeoCreated;
use App\Events\TargetGeo\TargetGeoDeleted;
use App\Events\TargetGeo\TargetGeoEdited;
use App\Events\TargetGeoRule\TargetGeoRuleCreated;
use App\Events\TargetGeoRule\TargetGeoRuleDeleted;
use App\Events\TargetGeoRule\TargetGeoRuleEdited;
use App\Models\Offer;
use App\Models\TargetGeoRule;
use Illuminate\Database\Eloquent\Builder;

class SyncOfferAdvertisers
{
    /**
     * @param TargetGeoCreated|TargetGeoEdited|TargetGeoRuleCreated|TargetGeoRuleEdited|TargetGeoDeleted|TargetGeoRuleDeleted $event
     */
    public function handle(Event $event)
    {
        $offer = $this->getOffer($event);

        $advertisers = $this->getOfferAdvertisers($offer);

        $offer->advertisers()->sync($advertisers);
    }

    private function getOffer(Event $event)
    {
        if (
            $event instanceof TargetGeoCreated ||
            $event instanceof TargetGeoEdited ||
            $event instanceof TargetGeoDeleted
        ) {
            return $event->target_geo->offer;
        }

        if (
            $event instanceof TargetGeoRuleCreated ||
            $event instanceof TargetGeoRuleEdited ||
            $event instanceof TargetGeoRuleDeleted
        ) {
            return $event->target_geo_rule->target_geo->offer;
        }

        throw new \BadMethodCallException();
    }

    private function getOfferAdvertisers(Offer $offer): array
    {
        return TargetGeoRule::whereHas('target_geo', function (Builder $builder) use ($offer) {
            return $builder->active()->where('offer_id', $offer['id']);
        })
            ->get()
            ->pluck('advertiser_id')->toArray();
    }
}
