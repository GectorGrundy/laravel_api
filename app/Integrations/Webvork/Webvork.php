<?php
declare(strict_types=1);

namespace App\Integrations\Webvork;

use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * Wrapper for Weblab API
 *
 * @package App\Integrations\Weblab
 */
class Webvork
{
    private const API_BASEPATH = 'http://api.webvork.com/v1';

    private $token;
    private $offer_id;
    private $name;
    private $phone;
    private $country;
    private $ip;

    /**
     * @var GuzzleHttpClient
     */
    private $http;

    public function __construct()
    {
        $this->http = new GuzzleHttpClient();
    }

    /**
     * Создание заказа в системе Webvork
     *
     * @param bool $log
     * @return array
     */
    public function createOrder(bool $log = false): array
    {
        $this->validateGetters(['token', 'offer_id', 'name', 'phone', 'country', 'ip']);

        $params = $this->getCreateOrderParams();

        $response = $this->makeOrder($params);

        if ($log) {
            $this->insertLog('create', $params, $response);
        }

        return $response;
    }

    private function validateGetters(array $fields): void
    {
        foreach ($fields as $field) {
            $getter = $this->getGetterName($field);

            if (\is_null($this->{$getter}())) {
                throw new \BadMethodCallException($this->getBadParameterExceptionMessage($field));
            }
        }
    }

    private function getGetterName($field): string
    {
        return 'get' . ucfirst(camel_case($field));
    }

    private function getBadParameterExceptionMessage(string $parameter): string
    {
        return $parameter . ' is not set';
    }

    private function getCreateOrderParams(): array
    {
        return [
            'token' => $this->getToken(),
            'offer_id' => $this->getOfferId(),
            'name' => $this->getName(),
            'phone' => $this->getPhone(),
            'country' => $this->getCountry(),
            'ip' => $this->getIp(),
        ];
    }

    private function makeOrder(array $body): array
    {
        $response = (string)$this->http->post(self::API_BASEPATH . '/new-lead', [
            'form_params' => $body
        ])->getBody();

        return \json_decode($response, true);
    }

    public function getOrders(array $external_keys, bool $log = false): array
    {
        $this->validateGetters(['token']);

        $params = $this->getParamsForGetOrders($external_keys);
        $response = $this->getOrdersRequest($params);

        if ($log) {
            $this->insertLog('update', $external_keys, $response);
        }

        return $response;
    }

    private function getOrdersRequest(array $params): array
    {
        $response = (string)$this->http->post(self::API_BASEPATH . '/get-lead-status', [
            'form_params' => $params
        ])->getBody();

        return \json_decode($response, true);
    }

    private function getParamsForGetOrders(array $external_keys): array
    {
        return [
            'guids' => $external_keys,
            'token' => $this->getToken()
        ];
    }

    private function insertLog(string $method, array $params, array $response): void
    {
        $log = "-----\n"
            . "Method:$method\n"
            . 'Date:' . date('d.m.Y H:i:s') . "\n"
            . 'Params: ' . json_encode($params) . "\n"
            . 'Response:' . json_encode($response)
            . "\n";

        \File::append(storage_path('/logs/webvork.log'), $log);
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getOfferId()
    {
        return $this->offer_id;
    }

    /**
     * @param mixed $offer_id
     */
    public function setOfferId($offer_id)
    {
        $this->offer_id = $offer_id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return GuzzleHttpClient
     */
    public function getHttp(): GuzzleHttpClient
    {
        return $this->http;
    }

    /**
     * @param GuzzleHttpClient $http
     */
    public function setHttp(GuzzleHttpClient $http)
    {
        $this->http = $http;
    }
}
