<?php

use App\Models\Locale;
use App\Models\OfferSourceTranslation;
use Illuminate\Database\Seeder;

class OfferSourceTranslationSeeder extends Seeder
{
    public function run()
    {
        OfferSourceTranslation::create([
            'offer_source_id' => 1,
            'locale_id' => Locale::EN,
            'title' => 'Web sites',
        ]);
        OfferSourceTranslation::create([
            'offer_source_id' => 2,
            'locale_id' => Locale::EN,
            'title' => 'Doorways',
        ]);
        OfferSourceTranslation::create([
            'offer_source_id' => 3,
            'locale_id' => Locale::EN,
            'title' => 'Contextual advertising',
        ]);
        OfferSourceTranslation::create([
            'offer_source_id' => 4,
            'locale_id' => Locale::EN,
            'title' => 'Contextual advertising for the brand',
        ]);
        OfferSourceTranslation::create([
            'offer_source_id' => 5,
            'locale_id' => Locale::EN,
            'title' => 'Teaser advertising',
        ]);
        OfferSourceTranslation::create([
            'offer_source_id' => 6,
            'locale_id' => Locale::EN,
            'title' => 'Targeted advertising',
        ]);
        OfferSourceTranslation::create([
            'offer_source_id' => 7,
            'locale_id' => Locale::EN,
            'title' => 'Social networks',
        ]);
        OfferSourceTranslation::create([
            'offer_source_id' => 8,
            'locale_id' => Locale::EN,
            'title' => 'Email-distribution',
        ]);
        OfferSourceTranslation::create([
            'offer_source_id' => 9,
            'locale_id' => Locale::EN,
            'title' => 'CashBack',
        ]);
        OfferSourceTranslation::create([
            'offer_source_id' => 10,
            'locale_id' => Locale::EN,
            'title' => 'ClickUnder/PopUnder',
        ]);
        OfferSourceTranslation::create([
            'offer_source_id' => 11,
            'locale_id' => Locale::EN,
            'title' => 'Brokers',
        ]);
        OfferSourceTranslation::create([
            'offer_source_id' => 12,
            'locale_id' => Locale::EN,
            'title' => 'Incentive',
        ]);
    }
}
