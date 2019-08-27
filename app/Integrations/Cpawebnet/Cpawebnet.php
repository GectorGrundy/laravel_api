<?php
declare(strict_types=1);

namespace App\Integrations\Cpawebnet;

class Cpawebnet
{
    private $user_id;
    private $offer_id;
    private $lead_hash;
    private $ip;
    private $user_agent;
    private $lead_country_code;

    public function __construct(
        int $user_id,
        int $offer_id,
        string $lead_hash,
        ?string $ip,
        ?string $user_agent,
        string $lead_country_code
    )
    {
        $this->user_id = $user_id;
        $this->offer_id = $offer_id;
        $this->lead_hash = $lead_hash;
        $this->ip = $ip;
        $this->user_agent = $user_agent;
        $this->lead_country_code = $lead_country_code;
    }

    public function run()
    {
        $url = $this->_getUrl();
        $response = $this->_request($url);

        return $this->_parseResult($response);
    }

    private function _getUrl(): string
    {
        return 'http://track.cpawebs.net/click?'
            . http_build_query([
                'pid' => $this->user_id,
                'offer_id' => $this->offer_id,
                'sub1' => $this->lead_hash
            ]);
    }

    private function _request($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_getHeaders());
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $a = curl_exec($ch);
        curl_close($ch);
        return $a;
    }

    private function _getHeaders()
    {
        return [
            'Accept: ',
            'Accept-Encoding: ',
            'Accept-Language: ',
            'Cache-Control: no-cache',
            'User-Agent: ' . $this->user_agent,
            'X-GeoIP-Country-Code: ' . $this->lead_country_code,
            'X-Real-IP: ' . $this->ip,
            'X-Forwarded-For: ' . $this->ip
        ];
    }

    private function _parseResult($response)
    {
        preg_match('#clickid=([a-z0-9]{24})#', $response, $matches);
        return (isset($matches[1]) && !empty($matches[1]) ? $matches[1] : false);
    }
}