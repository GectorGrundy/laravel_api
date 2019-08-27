<?php

use Illuminate\Database\Seeder;
use App\Models\OfferSource;

class OfferSourceSeeder extends Seeder
{
	public function run()
	{
        if (OfferSource::all()->count()) {
            return;
        }

		$offer_sources = ['веб-сайты', 'дорвеи', 'контекстная реклама', 'контекстная реклама на бренд',
			'тизерная реклама', 'таргетированная реклама', 'социальные сети', 'email-рассылка', 'CashBack',
			'ClickUnder/PopUnder', 'Брокеры', 'Incentive'];

		foreach ($offer_sources AS $offer_source) {
			OfferSource::create([
				'title' => $offer_source
			]);
		}
	}
}
