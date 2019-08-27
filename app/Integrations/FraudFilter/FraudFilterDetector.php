<?php
declare(strict_types=1);

namespace App\Integrations\FraudFilter;

use App\Contracts\CloakSystemDetector;
use App\Models\FraudfilterLog;

class FraudFilterDetector implements CloakSystemDetector
{
    private $api_key;
    private $campaign_id;
    private $customer_email;

    public function __construct(string $api_key, string $campaign_id, string $customer_email)
    {
        $this->api_key = $api_key;
        $this->campaign_id = $campaign_id;
        $this->customer_email = $customer_email;
    }

    public function isSafePage(): bool
    {
        $headers = $this->fillAllPostHeaders();

        $response = $this->sendRequest($headers);
        $is_safepage = $this->validateResponse($response);

        try {
            $this->insertLog($headers, $response, $is_safepage);
        } catch (\Exception $e) {

        }

        return $is_safepage;
    }

    private function validateResponse($output)
    {
        if ($output === '') {
            return true;
        }

        $result = $output[0];
        $sep = $output[1];

        if (($result !== '0' && $result !== '1') || $sep !== ';') {
            return true;
        }

        return $result === '0';
    }

    private function sendRequest($headers): string
    {
        $url = 'http://130.211.20.155/' . $this->campaign_id;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'FF-Customer:' . $this->customer_email);

        $output = curl_exec($ch);
        curl_close($ch);

        // curl_exec may also return false if result was failed
        if (is_bool($output) && $output === false) {
            return '';
        }

        return trim($output);
    }

    private function fillAllPostHeaders(): array
    {
        $headers = array();
        $headers[] = 'content-length: 0';
        $headers[] = 'X-FF-P: ' . $this->api_key;
        $this->addHeader($headers, 'X-FF-REMOTE-ADDR', 'REMOTE_ADDR');
        $this->addHeader($headers, 'X-FF-X-FORWARDED-FOR', 'HTTP_X_FORWARDED_FOR');
        $this->addHeader($headers, 'X-FF-X-REAL-IP', 'HTTP_X_REAL_IP');
        $this->addHeader($headers, 'X-FF-DEVICE-STOCK-UA', 'HTTP_DEVICE_STOCK_UA');
        $this->addHeader($headers, 'X-FF-X-OPERAMINI-PHONE-UA', 'HTTP_X_OPERAMINI_PHONE_UA');
        $this->addHeader($headers, 'X-FF-HEROKU-APP-DIR', 'HEROKU_APP_DIR');
        $this->addHeader($headers, 'X-FF-X-FB-HTTP-ENGINE', 'X_FB_HTTP_ENGINE');
        $this->addHeader($headers, 'X-FF-X-PURPOSE', 'X_PURPOSE');
        $this->addHeader($headers, 'X-FF-REQUEST-SCHEME', 'REQUEST_SCHEME');
        $this->addHeader($headers, 'X-FF-CONTEXT-DOCUMENT-ROOT', 'CONTEXT_DOCUMENT_ROOT');
        $this->addHeader($headers, 'X-FF-SCRIPT-FILENAME', 'SCRIPT_FILENAME');
        $this->addHeader($headers, 'X-FF-REQUEST-URI', 'REQUEST_URI');
        $this->addHeader($headers, 'X-FF-SCRIPT-NAME', 'SCRIPT_NAME');
        $this->addHeader($headers, 'X-FF-PHP-SELF', 'PHP_SELF');
        $this->addHeader($headers, 'X-FF-REQUEST-TIME-FLOAT', 'REQUEST_TIME_FLOAT');
        $this->addHeader($headers, 'X-FF-COOKIE', 'HTTP_COOKIE');
        $this->addHeader($headers, 'X-FF-ACCEPT-ENCODING', 'HTTP_ACCEPT_ENCODING');
        $this->addHeader($headers, 'X-FF-ACCEPT-LANGUAGE', 'HTTP_ACCEPT_LANGUAGE');
        $this->addHeader($headers, 'X-FF-CF-CONNECTING-IP', 'HTTP_CF_CONNECTING_IP');
        $this->addHeader($headers, 'X-FF-INCAP-CLIENT-IP', 'HTTP_INCAP_CLIENT_IP');
        $this->addHeader($headers, 'X-FF-QUERY-STRING', 'QUERY_STRING');
        $this->addHeader($headers, 'X-FF-X-FORWARDED-FOR', 'X_FORWARDED_FOR');
        $this->addHeader($headers, 'X-FF-ACCEPT', 'HTTP_ACCEPT');
        $this->addHeader($headers, 'X-FF-X-WAP-PROFILE', 'X_WAP_PROFILE');
        $this->addHeader($headers, 'X-FF-PROFILE', 'PROFILE');
        $this->addHeader($headers, 'X-FF-WAP-PROFILE', 'WAP_PROFILE');
        $this->addHeader($headers, 'X-FF-REFERER', 'HTTP_REFERER');
        $this->addHeader($headers, 'X-FF-HOST', 'HTTP_HOST');
        $this->addHeader($headers, 'X-FF-VIA', 'HTTP_VIA');
        $this->addHeader($headers, 'X-FF-CONNECTION', 'HTTP_CONNECTION');
        $this->addHeader($headers, 'X-FF-X-REQUESTED-WITH', 'HTTP_X_REQUESTED_WITH');
        $this->addHeader($headers, 'User-Agent', 'HTTP_USER_AGENT');
        $this->addHeader($headers, 'Expected', '');

        $hh = $this->getallheadersFF();
        $counter = 0;
        foreach ($hh as $key => $value) {
            $k = strtolower($key);
            if ($k === 'host') {
                $headers[] = 'X-FF-HOST-ORDER: ' . $counter;
                break;
            }
            ++$counter;
        }
        return $headers;
    }

    public function getallheadersFF(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    public function addHeader(& $headers, $out, $in)
    {
        if (!isset($_SERVER[$in])) {
            return;
        }
        $value = $_SERVER[$in];
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $headers[] = $out . ': ' . $value;
    }

    private function insertLog(array $headers, string $response, bool $is_safepage)
    {
        $fields = $this->headersToFields($headers);

        FraudfilterLog::create(array_merge($fields, [
            'response' => $response,
            'is_safepage' => $is_safepage,
            'campaign_id' => $this->campaign_id,
            'api_key' => $this->api_key,
        ]));
    }

    private function headersToFields($headers): array
    {
        $fields = [];
        $fillables = (new FraudfilterLog())->getFillable();

        foreach ($fillables as $field) {
            foreach ($headers as $header) {
                if (str_contains($header, $field . ':')) {
                    $fields[$field] = trim(substr($header, strlen($field) + 1));
                }
            }
        }

        return $fields;
    }
}
