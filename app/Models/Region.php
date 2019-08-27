<?php
declare(strict_types=1);

namespace App\Models;

use Cache;
use App\Models\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\Geoip\NotDetarmineRegionNames;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends AbstractEntity
{
    use HasTranslations;

    public const ON_DUPLICATE_KEY_ERROR_CODE = 23000;

    protected $fillable = ['country_id', 'geoname_id', 'title'];
    public $timestamps = false;

    public function getTitleAttribute($value)
    {
        return $this->getTranslatedAtribute('title', $value);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(RegionTranslation::class);
    }

    public function getInfoByGeonameId($geoname_id)
    {
        $key = __CLASS__ . __METHOD__ . $geoname_id;

        return Cache::get($key, function () use ($geoname_id, $key) {

            $region = self::where('geoname_id', $geoname_id)->firstOrFail();

            Cache::put($key, $region, 1440);

            return $region;
        });
    }

    /**
     * Получение идентификатора региона по данным об ip
     *
     * @param $country_id
     * @param $ip_data
     * @return int
     * @throws \Exception
     */
    public function getIdByIpData($country_id, $ip_data)
    {
        if (!isset($ip_data['subdivisions'][0]['geoname_id'])) {
            return 0;
        }

        try {
            $info = $this->getInfoByGeonameId($ip_data['subdivisions'][0]['geoname_id']);
            $region_id = $info['id'];
        } catch (ModelNotFoundException $e) {
            // Если нету инфо по региону - добавляем его
            try {
                $info = $this->createNew($country_id, $ip_data['subdivisions'][0]);
                $region_id = $info['id'];
            } catch (NotDetarmineRegionNames $e) {
                $region_id = 0;
            }
        }

        return $region_id;
    }

    /**
     * Добавление нового региона и переводов его названий для локалей, которые пришли в данных по ip адрессу
     *
     * @param $country_id
     * @param $subdivision_data
     * @return array
     */
    public function createNew($country_id, $subdivision_data)
    {
        if (!isset($subdivision_data['names']) || count($subdivision_data['names']) < 1) {
            throw new NotDetarmineRegionNames();
        }

        $geoname_id = $subdivision_data['geoname_id'];

        if (array_key_exists('ru', $subdivision_data['names'])) {
            $title = $subdivision_data['names']['ru'];
        } else if (array_key_exists('en', $subdivision_data['names'])) {
            $title = $subdivision_data['names']['en'];
        } else {
            $title = '';
        }

        try {
            $region_info = self::create([
                'title' => $title,
                'country_id' => $country_id,
                'geoname_id' => $geoname_id,
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() == self::ON_DUPLICATE_KEY_ERROR_CODE) {
                $region_info = $this->getInfoByGeonameId($geoname_id);
            }
        }

        // Добавляем переводы названия региона для существующих локалей
        foreach ($subdivision_data['names'] as $locale_code => $region_name) {

            try {
                $locale_info = Locale::getByCode($locale_code);

                RegionTranslation::create([
                    'region_id' => $region_info['id'],
                    'locale_id' => $locale_info['id'],
                    'title' => $region_name
                ]);

            } catch (ModelNotFoundException $e) {
                // Если нету данных по текущей локали - пропускаем ее
                continue;
            }
        }

        return $region_info;
    }
}
