<?php

use Illuminate\Database\Seeder;
use App\Models\Locale;

class LocaleSeeder extends Seeder
{
	public function run()
	{
        if (Locale::all()->count()) {
            return;
        }

		$locales = json_decode(File::get(storage_path('files/locales.json')), true);

		foreach ($locales AS $locale) {
			Locale::create(collect($locale)->except('thumb_path')->toArray());
		}

        if (!is_dir(storage_path('app/public/images/locales'))) {
            File::copyDirectory(
                storage_path('files/images/locales'),
                storage_path('app/public/images/locales')
            );
        }
	}
}
