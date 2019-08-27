<?php
declare(strict_types=1);

use Illuminate\Database\Seeder;
use App\Models\{
    Domain, Offer, Publisher, Flow
};
use Illuminate\Support\Collection;

class FlowSeeder extends Seeder
{
    public function run()
    {
        $offers = Offer::all();

        $publishers = Publisher::all();

        foreach ($offers as $offer) {
            foreach ($publishers as $publisher) {

                $target = $offer->targets->where('is_default', 1)->first();

                $flow = factory(Flow::class)->create([
                    'title' => $offer['title'],
                    'offer_id' => $offer['id'],
                    'publisher_id' => $publisher['id'],
                    'target_id' => $target['id'],
                ]);

                $this->attachLandings($flow, $target->landings);
                $this->attachTransits($flow, $target->transits);
            }
        }
    }

    private function attachLandings(Flow $flow, Collection $landings)
    {
        foreach ($landings AS $landing) {
            DB::table('flow_landing')->insert([
                'flow_id' => $flow['id'],
                'landing_id' => $landing['id'],
            ]);
        }
    }

    private function attachTransits(Flow $flow, Collection $transits)
    {
        foreach ($transits AS $transit) {
            DB::table('flow_transit')->insert([
                'flow_id' => $flow['id'],
                'transit_id' => $transit['id'],
            ]);
        }
    }
}
