<?php
declare(strict_types=1);

namespace App\Integrations\Finaro;

use Illuminate\Console\Command;
use App\Exceptions\Integration\IncorrectLeadStatusException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\{
	Integration,
	Lead
};
use Log;

class FinaroUpdateOrderList extends Command
{
    public const TRASH_STATUSES = [4, 5, 14];
    public const STATUS_PROCESSING = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_CANCELLED = 3;

	protected $signature = 'finaro:update_order_list {integration_id}';
	protected $description = 'Get orders from finaro integration which was updated';

	private $integration;
	private $lead;

	public function __construct(Integration $integration, Lead $lead)
	{
		parent::__construct();

		$this->integration = $integration;
		$this->lead = $lead;
	}

	public function handle()
	{
		$integration_id = (int)$this->argument('integration_id');

		// Получаем информацию по интеграции Call-ninja
		$integration_info = $this->integration->getById($integration_id);

		$params_string = http_build_query(
			array_merge(
				[
					'ts_start' => strtotime('-7 days'),
					'ts_end' => time()
				],
				json_decode($integration_info['integration_data'], true)
			)
		);

		$url = "https://api.firano.ru/api/order/status?{$params_string}";

		// Отправка запроса
		$result = sendIntegrationRequest($url);

		$curl_info = $result['curl_info'];
		if ($curl_info['http_code'] != 200) {
			Log::error('Finaro возвращает не 200 код', ['integration_id' => $integration_id]);
			return false;
		}

		$response = json_decode($result['response'], true);
		if (!isset($response['code']) || $response['code'] != 'ok') {
			Log::error('Call-ninja возвращает ошибку в методе order.listUpdate', ['integration_id' => $integration_id]);
			return false;
		}

		foreach ($response['data'] AS $order) {

			if (!isset($order['order_id'], $order['status'])
				|| $order['status'] === 'hold'
				|| $order['status'] === 'processing'
				|| $order['status'] === 'unknown'
			) {
				continue;
			}

			try {

				$lead = $this->lead->getByExternalKey($integration_id, $order['order_id']);

			} catch (ModelNotFoundException $e) {

				Log::error('Не найден лид по заданному external_key', [
					'integration_id' => $integration_id,
					'order_id' => $order['order_id'],
					'status' => $order['status']
				]);
				continue;
			}

			$sub_status_id = 0;
			$sub_status = '';
			switch ($order['status']) {
				case 'trash':
					$status = 'trashed';
					$sub_status = $order['extra_status'];
					break;

				case 'cancelled':
					$status = 'cancelled';
					$sub_status = $order['extra_status'];
					break;

				case 'bill':
					$status = 'approved';

					switch ($order['extra_status']) {
						case 'bought':
							$sub_status_id = 21;
							break;

						case 'canceled_after_confirmation':
							$sub_status_id = 22;
							break;

						case 'returned':
							$sub_status_id = 23;
							break;
					}
					break;

				default:
					throw new IncorrectLeadStatusException($lead['id'], $order['status']);
			}

			// Если статус лида НЕ "в обработке" и пришел статус, отличающийся от текущего статуса лида - ошибка
			if ($lead['status'] !== 'new' && $lead['status'] !== $status) {

				Log::error('Finaro вернул для обработанного заказа новый статус', [
					'integration_id' => $integration_id,
					'order_id' => $order['order_id'],
					'status' => $status
				]);
				continue;
			}

			switch ($status) {
				case 'approved':
                    $lead->approve($sub_status_id, $sub_status);
					break;

				case 'cancelled':
					$lead->cancel($sub_status_id, $sub_status);
					break;

				case 'trashed':
					$lead->trash($sub_status_id, $sub_status);
					break;
			}
		}
	}
}

