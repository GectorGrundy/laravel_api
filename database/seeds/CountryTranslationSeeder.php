<?php
declare(strict_types=1);

use Illuminate\Database\Seeder;

class CountryTranslationSeeder extends Seeder
{
    public function run()
    {
        if (count(DB::table('country_translations')->get())) {
            return;
        }

        $country_translations = json_decode(File::get(storage_path('files/country_translations.json')), true);

        foreach ($country_translations AS $country_translation) {

            DB::table('country_translations')->insert([
                'country_id' => $country_translation['country_id'],
                'locale_id' => $country_translation['locale_id'],
                'title' => $country_translation['title'],
            ]);
        }
    }

    private function parse()
    {
        $translations = explode("\n", str_replace(['"'], '', \File::get(base_path('new_country.txt'))));

        $str = '';
        $i = 1;
        foreach ($translations as $title) {
            $str .= ',{"country_id":' . $i . ',"locale_id":46,"title":"' . $title . '"}';
            $i++;
        }
        dd($str);
    }
}
