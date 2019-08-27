<?php
declare(strict_types=1);

namespace App\Services;

class GoUtmParameters
{
    private function getParameterValue(string $parameter, string $alias, array $cache = []): string
    {
        if (request()->filled($parameter)) {
            return request()->get($parameter);
        }

        if (request()->filled($alias)) {
            return request()->get($alias);
        }

        return $cache[$parameter] ?? '';
    }

    public static function getParamName(string $data_param): string
    {
        switch ($data_param) {
            case 'data1':
                return request()->get('utm_content') ? 'utm_content' : 'data1';

            case 'data2':
                return request()->get('utm_medium') ? 'utm_medium' : 'data2';

            case 'data3':
                return request()->get('utm_source') ? 'utm_source' : 'data3';

            case 'data4':
                return request()->get('utm_campaign') ? 'utm_campaign' : 'data4';
        }
    }

    public  function getData1($tds_parameters): string
    {
        return $this->getParameterValue('data1', 'utm_content', $tds_parameters);
    }

    public function getData2($tds_parameters): string
    {
        return $this->getParameterValue('data2', 'utm_medium', $tds_parameters);
    }

    public function getData3($tds_parameters): string
    {
        return $this->getParameterValue('data3', 'utm_source', $tds_parameters);
    }

    public function getData4($tds_parameters): string
    {
        return $this->getParameterValue('data4', 'utm_campaign', $tds_parameters);
    }
}
