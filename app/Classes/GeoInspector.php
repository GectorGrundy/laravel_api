<?php
declare(strict_types=1);

namespace App\Classes;

use App\Exceptions\Geoip\NotDetarmineCountryCode;
use GeoIp2\Exception\AddressNotFoundException;
use App\Models\{
    Country, Region, City
};

class GeoInspector
{
    /**
     * Получение идентификаторов страны, региона и города по ip адрессу
     *
     * @param string $ip
     * @return array
     *
     * @throws \Exception
     */
    public function getGeoIds(string $ip): array
    {
        try {
            $ip_data = getIpInfo($ip);
        } catch (AddressNotFoundException $e) {
            return [
                'country_id' => 0,
                'region_id' => 0,
                'city_id' => 0,
                'country_code' => 'en',
            ];
        }

        if (isset($ip_data['country']['iso_code'])) {
            $country = (new Country())->getByCode($ip_data['country']['iso_code']);
            $visitor_country_id = $country['id'];
            $visitor_country_code = $country['code'];
        } else {
            $visitor_country_id = 0;
            $visitor_country_code = 'en';
        }

        if ($visitor_country_id > 0) {
            $visitor_region_id = (new Region())->getIdByIpData($visitor_country_id, $ip_data);
            $visitor_city_id = (new City())->getIdByIpData($visitor_country_id, $visitor_region_id, $ip_data);
        } else {
            $visitor_region_id = 0;
            $visitor_city_id = 0;
        }

        return [
            'country_id' => $visitor_country_id,
            'region_id' => $visitor_region_id,
            'city_id' => $visitor_city_id,
            'country_code' => $visitor_country_code,
        ];
    }
}