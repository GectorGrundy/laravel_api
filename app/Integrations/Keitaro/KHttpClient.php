<?php
declare(strict_types=1);

namespace App\Integrations\Keitaro;

class KHttpClient
{
    public const UA = 'KHttpClient';

    public function request($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, self::UA);
        $result = curl_exec($ch);
        if (curl_error($ch)) {
            throw new KTrafficClientError(curl_error($ch));
        }

        if (empty($result)) {
            throw new KTrafficClientError('Empty response');
        }
        return $result;
    }
}