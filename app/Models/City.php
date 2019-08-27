<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasTranslations;
use Cache;
use Illuminate\Database\Eloquent\{
    Model, ModelNotFoundException
};
use App\Exceptions\Geoip\NotDetarmineCityNames;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends AbstractEntity
{
    use HasTranslations;

    public const ON_DUPLICATE_KEY_ERROR_CODE = 23000;

    protected $fillable = ['country_id', 'region_id', 'geoname_id', 'title'];
    public $timestamps = false;

    public function getTitleAttribute($value)
    {
        return $this->getTranslatedAtribute('title', $value);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CityTranslation::class);
    }

    public function getInfoByGeonameId($geoname_id)
    {
        $key = __CLASS__ . __METHOD__ . $geoname_id;

        return Cache::get($key, function () use ($geoname_id, $key) {

            $city = self::where('geoname_id', $geoname_id)->firstOrFail();

            Cache::put($key, $city, 1440);

            return $city;
        });
    }

    /**
     * Получение идентификатора города по данным об ip
     *
     * @param $country_id
     * @param $region_id
     * @param $ip_data
     *
     * @return int
     *
     * @throws \Exception
     */
    public function getIdByIpData($country_id, $region_id, $ip_data): int
    {
        if (!isset($ip_data['city']['geoname_id'])) {

            $city_id = 0;

        } else {

            try {

                $info = $this->getInfoByGeonameId($ip_data['city']['geoname_id']);
                $city_id = $info['id'];

            } catch (ModelNotFoundException $e) {

                // Если нету инфо по городу - добавляем его
                try {

                    $info = $this->createNew($country_id, $region_id, $ip_data['city']);
                    $city_id = $info['id'];

                } catch (NotDetarmineCityNames $e) {

                    $city_id = 0;
                }
            }
        }

        return $city_id;
    }

    /**
     * Добавление нового города и переводов его названий для локалей, которые пришли в данных по ip адрессу
     *
     * @param $country_id
     * @param $region_id
     * @param $city_data
     * @return array
     */
    public function createNew($country_id, $region_id, $city_data)
    {
        if (!isset($city_data['names']) || count($city_data['names']) < 1) {
            throw new NotDetarmineCityNames();
        }

        $geoname_id = $city_data['geoname_id'];

        if (array_key_exists('ru', $city_data['names'])) {

            $title = $city_data['names']['ru'];

        } else if (array_key_exists('en', $city_data['names'])) {

            $title = $city_data['names']['en'];

        } else {

            $title = '';
        }

        try {

            $city_info = self::create([
                'title' => $title,
                'country_id' => $country_id,
                'region_id' => $region_id,
                'geoname_id' => $geoname_id,
            ]);

        } catch (\PDOException $e) {

            if ($e->getCode() == self::ON_DUPLICATE_KEY_ERROR_CODE) {

                $city_info = $this->getInfoByGeonameId($geoname_id);
            }
        }

        // Добавляем переводы названия города для существующих локалей
        foreach ($city_data['names'] as $locale_code => $city_name) {

            try {

                $locale_info = Locale::getByCode($locale_code);

                CityTranslation::create([
                    'city_id' => $city_info['id'],
                    'locale_id' => $locale_info['id'],
                    'title' => $city_name
                ]);

            } catch (ModelNotFoundException $e) {
                // Если нету данных по текущей локали - пропускаем ее
                continue;
            }
        }

        return $city_info;
    }
}
