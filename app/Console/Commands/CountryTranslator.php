<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\{
    Country, Locale
};
use Illuminate\Console\Command;
use App\Services\MicrosoftTranslation\MicrosoftTranslationService;

class CountryTranslator extends Command
{
    protected $signature = 'country:generate_file';
    protected $description = 'Generate json file of countries title translations';

    public function handle()
    {
        $countries = Country::get();
        $locales = Locale::get();

        $progress = $this->output->createProgressBar(count($countries));

        foreach ($countries AS $country) {
            foreach ($locales AS $locale) {

                $translation_exists = \DB::table('country_translations')
                    ->where('country_id', $country['id'])
                    ->where('locale_id', $locale['id'])
                    ->exists();

                if ($translation_exists) {
                    continue;
                }

                $translate_to = $locale['code'];

                if ($translate_to === 'bs') {
                    $translate_to = 'bs-Latn';
                }

                if ($translate_to === 'zh') {
                    $translate_to = 'zh-CHS';
                }

                $translated_title = MicrosoftTranslationService::translate('ru', $translate_to, $country['title']);

                \DB::table('country_translations')->insert([
                    'country_id' => $country['id'],
                    'locale_id' => $locale['id'],
                    'title' => $translated_title,
                ]);
            }

            $progress->advance();
        }

        $translations = \DB::table('country_translations')->get();

        $result = [];
        foreach ($translations as $translation) {

            $result[] = [
                'country_id' => $translation->country_id,
                'locale_id' => $translation->locale_id,
                'title' => $translation->title,
            ];
        }

        \Storage::put('country_translations.json', json_encode($result, JSON_UNESCAPED_UNICODE));

        $progress->finish();

        $this->info("\n ( ͡° ͜ʖ ͡°) \n");
    }
}
