<?php
declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use App\Models\{
    Landing, Target, TargetGeo, TargetGeoRule, Transit, Offer
};

class OfferSeeder extends Seeder
{
    public function run()
    {
        $offer = $this->createChocolite();

        $this->createEntitiesByStabs();

        $this->extraOfferSetting($offer);
    }

    private function createChocolite(): Offer
    {
        return factory(Offer::class)->create([
            'title' => 'Choco Lite',
            'description' => 'An effective chocolate cocktail for intensive weight loss. A tasty and actionable product to make you slimmer.',
            'agreement' => 'no rules, no limits, no fear'
        ]);
    }

    private function extraOfferSetting(Offer $offer)
    {
        $offer->saveImage(SeederConstants::RANDOM_IMAGE_PATH);
        $offer->advertisers()->sync($this->getOfferAdvertisers($offer));
        $offer->offer_categories()->attach([1]);
        $offer->offer_sources()->attach([1, 2, 3, 4, 5, 6, 7, 8, 10]);
    }

    private function createEntitiesByStabs()
    {
        $targets = include __DIR__ . '/stabs/targets.php';
        foreach ($targets as $target) {
            Target::create($target);
        }

        $target_geo_list = include __DIR__ . '/stabs/target_geo.php';
        foreach ($target_geo_list as $target_geo) {
            TargetGeo::create($target_geo);
        }

        $rules = include __DIR__ . '/stabs/target_geo_rules.php';
        foreach ($rules as $rule) {
            TargetGeoRule::create($rule);
        }

        $this->seedLandings();
        $this->seedTransits();
    }

    private function seedLandings()
    {
        $landings = include __DIR__ . '/stabs/landings.php';
        foreach ($landings as $landing) {

            $new_landing = Landing::create($landing);
            $new_landing->saveImage(SeederConstants::RANDOM_IMAGE_PATH);

            event(new \App\Events\LandingCreated(
                $new_landing,
                $new_landing->getOriginal(),
                config('env.landings_path') . '/cod/' . $landing['subdomain']
            ));
            $new_landing->saveImage(SeederConstants::RANDOM_IMAGE_PATH);
        }
    }

    private function seedTransits()
    {
        $transits = include __DIR__ . '/stabs/transits.php';
        foreach ($transits as $transit) {

            $new_transit = Transit::create($transit);

            event(new \App\Events\TransitCreated(
                $new_transit,
                $new_transit->getOriginal(),
                config('env.landings_path') . '/transit/' . $transit['subdomain']
            ));

            $new_transit->saveImage(SeederConstants::RANDOM_IMAGE_PATH);
        }
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
