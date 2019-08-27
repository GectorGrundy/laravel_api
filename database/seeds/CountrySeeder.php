<?php

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run()
    {
        if (Country::all()->count()) {
            return;
        }

        $countries = json_decode(File::get(storage_path('files/countries.json')), true);

        foreach ($countries AS $country) {

            unset($country['thumb_path']);

            Country::create(array_merge($country, [
                'is_active' => isset($country['is_active']) ? $country['is_active'] : 0
            ]));
        }

        if (!is_dir(storage_path('app/public/images/countries'))) {
            File::copyDirectory(
                storage_path('files/images/countries'),
                storage_path('app/public/images/countries')
            );
        }
    }
}
