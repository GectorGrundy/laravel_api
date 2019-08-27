<?php
declare(strict_types=1);

namespace App\Console\Commands;

use DB;
use App\Models\Offer;
use Illuminate\Console\Command;

class OpenEntitiesToAdvertisers extends Command
{
    protected $signature = 'advertiser:open_entities';
    protected $description = '';

    public function handle()
    {
        $offers = Offer::with(['targets.target_geo.target_geo_rules'])->get();

        foreach ($offers as $offer) {
            $offer->advertisers()->sync($this->getAdvertiserIds($offer));
        }

        DB::table('landings')->update([
            'is_advertiser_viewable' => 1,
        ]);
        DB::table('transits')->update([
            'is_advertiser_viewable' => 1,
        ]);
    }

    private function getAdvertiserIds(Offer $offer)
    {
        $advertisers = [];
        foreach ($offer->targets as $target) {
            foreach ($target->target_geo as $target_geo) {
                foreach ($target_geo->target_geo_rules as $target_geo_rule) {
                    $advertisers[] = $target_geo_rule['advertiser_id'];
                }
            }
        }

        return array_unique($advertisers);
    }
}
