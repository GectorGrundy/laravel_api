<?php

use Illuminate\Database\Seeder;
use App\Models\Offer;
use App\Models\Transit;
use App\Models\Target;

class TransitSeeder extends Seeder
{
	public function run()
	{
		$faker = Faker\Factory::create();

		$offers = Offer::all()->pluck('id')->toArray();

		foreach ($offers AS $offer_id) {

			$targets = Target::where('offer_id', $offer_id)->lists('id')->toArray();

			foreach ($targets AS $target_id) {

				$subdomain = strtolower($faker->word);

				$transit = Transit::create([
					'title' => $subdomain,
					'subdomain' => $subdomain,
					'offer_id' => $offer_id,
					'target_id' => $target_id,
					'locale_id' => 1,
					'is_private' => 0,
					'is_mobile' => random_int(0, 1),
					'is_active' => 1,
					'is_responsive' => 1,
				]);

                $transit->saveImage(storage_path('files/example.png'));
			}
		}

	}
}
