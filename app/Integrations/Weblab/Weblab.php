<?php
declare(strict_types = 1);

namespace App\Integrations\Weblab;

use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * Wrapper for Weblab API
 *
 * @package App\Integrations\Weblab
 */
class Weblab
{
	private const API_BASEPATH = 'http://www.weblab.co.rs';

	private $name;
	private $phone;
	private $partner_id;
	private $product_id;
	private $transaction_id;
	/**
	 * @var GuzzleHttpClient
	 */
	private $http;

	public function __construct()
	{
		$this->http = new GuzzleHttpClient();
	}

	/**
	 * Создание заказа в системе Weblab
	 *
	 * @param bool $log
	 * @return array
	 */
	public function createOrder(bool $log = false): array
	{
		$this->validateGetters(['name', 'phone', 'partner_id', 'product_id', 'transaction_id']);

		$params = $this->getCreateOrderParams();

		$response = $this->sentCreateOrderRequest($params);

		if ($log) {
			$this->insertLog('create', $params, $response);
		}

		return $response;
	}

	public function checkOrderStatuses(array $order_ids, bool $log = false): array
	{
		$this->validateGetters(['partner_id']);

		$params = $this->getParamsForCheckOrders($order_ids);

		$response = $this->sentCheckOrderStatusesRequest($params);

		if ($log) {
			$this->insertLog('update', $params, $response);
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
		$phone = $this->getPhone();
		$partner_id = $this->getPartnerId();
		$product_id = $this->getProductId();
		$check_sum = md5(implode('_', [$partner_id, $product_id, $phone]));

		return [
			'name' => $this->getName(),
			'phone' => $phone,
			'partner' => $partner_id,
			'product' => $product_id,
			'transaction_id' => $this->getTransactionId(),
			'check_sum' => $check_sum,
		];
	}

	private function getParamsForCheckOrders(array $order_ids): array
	{
		$partner_id = $this->getPartnerId();

		return [
			'ids' => implode(',', $order_ids),
			'partner' => $partner_id,
		];
	}

	private function sentCreateOrderRequest(array $body): array
	{
		$multipart = [];

		foreach ($body as $name => $contents) {
			$multipart[] = [
				'name' => $name,
				'contents' => $contents
			];
		}

		$response = (string)$this->http->request('POST', self::API_BASEPATH . '/call_center_master/insert_purchase', [
			'multipart' => $multipart
		])->getBody();

		return \json_decode($response, true);
	}

	private function sentCheckOrderStatusesRequest(array $body): array
	{
		$response = (string)$this->http->post(self::API_BASEPATH . '/call_center_master/get_orders', [
			'form_params' => $body
		])->getBody();

		$bad_out_pos = strpos($response, '<br/>[{');

		if ($bad_out_pos !== false) {
			$response = substr($response, ($bad_out_pos + strlen('<br/>')));
		}

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

		\File::append(storage_path('/logs/weblab.log'), $log);
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
	public function setName($name): void
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
	public function setPhone($phone): void
	{
		$this->phone = str_replace("+", "", $phone);
	}

	/**
	 * @return mixed
	 */
	public function getPartnerId()
	{
		return $this->partner_id;
	}

	/**
	 * @param mixed $partner_id
	 */
	public function setPartnerId($partner_id): void
	{
		$this->partner_id = $partner_id;
	}

	/**
	 * @return mixed
	 */
	public function getProductId()
	{
		return $this->product_id;
	}

	/**
	 * @param mixed $product_id
	 */
	public function setProductId($product_id): void
	{
		$this->product_id = $product_id;
	}

	/**
	 * @return mixed
	 */
	public function getTransactionId()
	{
		return $this->transaction_id;
	}

	/**
	 * @param mixed $transaction_id
	 */
	public function setTransactionId($transaction_id): void
	{
		$this->transaction_id = $transaction_id;
	}
}