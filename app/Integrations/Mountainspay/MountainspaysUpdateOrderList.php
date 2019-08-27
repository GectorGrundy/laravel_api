<?php
declare(strict_types=1);

namespace App\Integrations\Mountainspay;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\{
	Integration, Lead
};
use Log;

class MountainspaysUpdateOrderList extends Command
{
	protected $signature = 'mountainspays:update_order_list {integration_id}';

	protected $description = 'Get orders from mountainspays integration';

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
		$integration_info = $this->integration->getById($integration_id);


		$this->checkNewLeads($integration_id, $integration_info);
		$this->checkApprovedLeads($integration_id, $integration_info);
	}

    /**
     * Получение статусов для новых лидов
     *
     * @param $integration_id
     * @param $integration_info
     * @throws \App\Exceptions\Currency\IncorrectCodeException
     * @throws \App\Exceptions\Integration\IncorrectLeadStatusException
     */
	private function checkNewLeads($integration_id, $integration_info)
	{

		Lead::select('external_key')->where('integration_id', $integration_id)
			->where('status', 'new')
            ->orderBy('id')
			->chunk(100, function ($leads) use ($integration_info, $integration_id) {

				$order_ids = [];
				foreach ($leads AS $lead) {
					$order_ids[] = $lead['external_key'];
				}

				$params_string = http_build_query(
					array_merge(
						['order_ids' => implode(',', $order_ids)],
						json_decode($integration_info['integration_data'], true)
					)
				);

				$url = "http://mountainspay.com/api/v1/get-orders-status?{$params_string}";

				// Отправка запроса
				$result = sendIntegrationRequest($url);

				$curl_info = $result['curl_info'];
				if ($curl_info['http_code'] != 200) {
					Log::error('Mountainspays возвращает не 200 код', ['integration_id' => $integration_id]);
					return false;
				}

				$response = json_decode($result['response'], true);

				foreach ($response AS $order) {

					if (!isset($order['order_id']) || !isset($order['status'])) {
						continue;
					}

					try {
						$lead = $this->lead->getByExternalKey($integration_id, $order['order_id']);
					} catch (ModelNotFoundException $e) {
						$this->logUnknownLeadError($integration_id, $order['order_id'], $order['status']);
						continue;
					}

					$sub_status_id = $this->getSubstatusId($order['status']);

					if (config('integrations.mountainspay.status_pairs.' . $order['status']) === 'approved') {
                        $lead->approve($sub_status_id);

					} elseif (config('integrations.mountainspay.status_pairs.' . $order['status']) === 'cancelled') {
						$lead->cancel($sub_status_id);

					} elseif (config('integrations.mountainspay.status_pairs.' . $order['status']) === 'trashed') {
						$lead->trash($sub_status_id);

					} elseif (config('integrations.mountainspay.status_pairs.' . $order['status']) === 'new') {
						if ($sub_status_id !== $lead['sub_status_id']) {
                            $lead->update([
                                'sub_status_id' => $sub_status_id,
                            ]);
						}
					}
				}
			});
	}

	/**
	 * Получение статусов для подтвержденных лидов
	 *
	 * @param $integration_id
	 * @param $integration_info
	 */
	private function checkApprovedLeads($integration_id, $integration_info)
	{
		Lead::select('external_key')->where('integration_id', $integration_id)
			->where('status', 'approved')
			->where('sub_status_id', 0)
            ->orderBy('id')
			->chunk(100, function ($leads) use ($integration_info, $integration_id) {

				$order_ids = [];
				foreach ($leads AS $lead) {
					$order_ids[] = $lead['external_key'];
				}

				$params_string = http_build_query(
					array_merge(
						['order_ids' => implode(',', $order_ids)],
						json_decode($integration_info['integration_data'], true)
					)
				);

				$url = "http://mountainspay.com/api/v1/get-orders-status?{$params_string}";

				// Отправка запроса
				$result = sendIntegrationRequest($url);

				$curl_info = $result['curl_info'];
				if ($curl_info['http_code'] !== 200) {
					Log::error('Mountainspays возвращает не 200 код', ['integration_id' => $integration_id]);
					return false;
				}

				$response = json_decode($result['response'], true);

				foreach ($response AS $order) {

					if (!isset($order['order_id'], $order['status'])) {
						continue;
					}

					try {

						$lead_info = $this->lead->getByExternalKey($integration_id, $order['order_id']);

					} catch (ModelNotFoundException $e) {

						$this->logUnknownLeadError($integration_id, $order['order_id'], $order['status']);
						continue;
					}

					$sub_status_id = $this->getSubstatusId($order['status']);
					if ($sub_status_id !== 0) {
						Lead::find($lead_info['id'])->update([
							'sub_status_id' => $sub_status_id
						]);
					}
				}
			});
	}

	/**
	 * Получаем идентификатор нашего субстатуса на основании присланного от mountainspay
	 *
	 * @param $status
	 * @return int|mixed
	 */
	private function getSubstatusId($status): int
	{
		$substatus_id = 0;
		if (null !== config('integrations.mountainspay.substatus_pairs.' . $status)) {
			$substatus_id = config('integrations.mountainspay.substatus_pairs.' . $status);
		}

		return (int)$substatus_id;
	}

	/**
	 * Запись лога ошибки, если не найден лид по нужным параметрам
	 *
	 * @param $integration_id
	 * @param $order_id
	 * @param $status
	 */
	private function logUnknownLeadError($integration_id, $order_id, $status)
	{
		Log::error('Не найден лид по заданному external_key', [
			'integration_id' => $integration_id,
			'order_id' => $order_id,
			'status' => $status
		]);
	}
}

