<?php
declare(strict_types=1);

namespace App\Integrations\Magichygeia;

use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * Wrapper for Magichygeia API
 *
 * @package App\Integrations\Magichygeia
 */
class Magichygeia
{
    private const API_BASEPATH = 'http://apieu.mhorderceate.com';

    private $product_title; //title
    private $product_combo; //combo
    private $product_number; //pn
    private $product_size; //size
    private $product_count; //count

    private $customer_name; //name
    private $customer_address; //address
    private $customer_message; //message
    private $customer_city; //city
    private $customer_phone; //phone

    private $payment_way; // pay_way
    private $payment_amount; //money
    private $province; //province
    private $self_key; //self_key
    private $mark_region; //area

    /**
     * @var GuzzleHttpClient
     */
    private $http;

    public function __construct()
    {
        $this->http = new GuzzleHttpClient();
    }

    /**
     * Создание заказа в системе Magichygeia
     *
     * @param bool $log
     * @return array
     */
    public function createOrder(bool $log = false): array
    {
        $this->validateGetters([
            'product_title', 'product_combo', 'product_number', 'product_size', 'product_count',
            'customer_name', 'customer_address', 'customer_message', 'customer_city', 'customer_phone',
            'payment_way', 'payment_amount', 'province', 'self_key', 'mark_region',
        ]);

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
            'title' => $this->getProductTitle(),
            'combo' => $this->getProductCombo(),
            'pn' => $this->getProductNumber(),
            'size' => $this->getProductSize(),
            'count' => $this->getProductCount(),

            'name' => $this->getCustomerName(),
            'address' => $this->getCustomerAddress(),
            'message' => $this->getCustomerMessage(),
            'city' => $this->getCustomerCity(),
            'phone' => $this->getCustomerPhone(),

            'pay_way' => $this->getPaymentWay(),
            'money' => $this->getPaymentAmount(),
            'province' => $this->getProvince(),
            'self_key' => $this->getSelfKey(),
            'area' => $this->getMarkRegion(),
        ];
    }

    private function makeOrder(array $params): array
    {
        dd($params);
        $response = (string)$this->http->post(self::API_BASEPATH . '/post_create_bill', [
            'form_params' => $params
        ])->getBody();

        return \json_decode($response, true);
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
    public function getProductTitle()
    {
        return $this->product_title;
    }

    /**
     * @param mixed $product_title
     */
    public function setProductTitle($product_title)
    {
        $this->product_title = $product_title;
    }

    /**
     * @return mixed
     */
    public function getProductCombo()
    {
        return $this->product_combo;
    }

    /**
     * @param mixed $product_combo
     */
    public function setProductCombo($product_combo)
    {
        $this->product_combo = $product_combo;
    }

    /**
     * @return mixed
     */
    public function getProductNumber()
    {
        return $this->product_number;
    }

    /**
     * @param mixed $product_number
     */
    public function setProductNumber($product_number)
    {
        $this->product_number = $product_number;
    }

    /**
     * @return mixed
     */
    public function getProductSize()
    {
        return $this->product_size;
    }

    /**
     * @param mixed $product_size
     */
    public function setProductSize($product_size)
    {
        $this->product_size = $product_size;
    }

    /**
     * @return mixed
     */
    public function getProductCount()
    {
        return $this->product_count;
    }

    /**
     * @param mixed $product_count
     */
    public function setProductCount($product_count)
    {
        $this->product_count = $product_count;
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customer_name;
    }

    /**
     * @param mixed $customer_name
     */
    public function setCustomerName($customer_name)
    {
        $this->customer_name = $customer_name;
    }

    /**
     * @return mixed
     */
    public function getCustomerAddress()
    {
        return $this->customer_address;
    }

    /**
     * @param mixed $customer_address
     */
    public function setCustomerAddress($customer_address)
    {
        $this->customer_address = $customer_address;
    }

    /**
     * @return mixed
     */
    public function getCustomerMessage()
    {
        return $this->customer_message;
    }

    /**
     * @param mixed $customer_message
     */
    public function setCustomerMessage($customer_message)
    {
        $this->customer_message = $customer_message;
    }

    /**
     * @return mixed
     */
    public function getCustomerCity()
    {
        return $this->customer_city;
    }

    /**
     * @param mixed $customer_city
     */
    public function setCustomerCity($customer_city)
    {
        $this->customer_city = $customer_city;
    }

    /**
     * @return mixed
     */
    public function getPaymentWay()
    {
        return $this->payment_way;
    }

    /**
     * @param mixed $payment_way
     */
    public function setPaymentWay($payment_way)
    {
        $this->payment_way = $payment_way;
    }

    /**
     * @return mixed
     */
    public function getPaymentAmount()
    {
        return $this->payment_amount;
    }

    /**
     * @param mixed $payment_amount
     */
    public function setPaymentAmount($payment_amount)
    {
        $this->payment_amount = $payment_amount;
    }

    /**
     * @return mixed
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * @param mixed $province
     */
    public function setProvince($province)
    {
        $this->province = $province;
    }

    /**
     * @return mixed
     */
    public function getSelfKey()
    {
        return $this->self_key;
    }

    /**
     * @param mixed $self_key
     */
    public function setSelfKey($self_key)
    {
        $this->self_key = $self_key;
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

    /**
     * @return mixed
     */
    public function getMarkRegion()
    {
        return $this->mark_region;
    }

    /**
     * @param mixed $mark_region
     */
    public function setMarkRegion($mark_region)
    {
        $this->mark_region = $mark_region;
    }

    /**
     * @return mixed
     */
    public function getCustomerPhone()
    {
        return $this->customer_phone;
    }

    /**
     * @param mixed $customer_phone
     */
    public function setCustomerPhone($customer_phone)
    {
        $this->customer_phone = $customer_phone;
    }

}
