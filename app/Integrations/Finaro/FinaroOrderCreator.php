<?php
declare(strict_types=1);

namespace App\Integrations\Finaro;

use App\Integrations\ReleaseJob;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Exceptions\Integration\IncorrectExternalKey;

/**
 * @deprecated
 */
class FinaroOrderCreator implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
	use ReleaseJob;

	private $data;

	public function __construct($data)
	{
		$this->data = $data;
	}

	public function handle()
	{
        $params_string = http_build_query(
			array_merge(
				[
					'ip' => $this->data['lead_info']['ip'],
					'msisdn' => $this->data['lead_info']['order']['phone'],
					'name' => $this->data['lead_info']['order']['name'],
					'sa' => $this->data['lead_info']['publisher']['hash'],
					'client_type' => $this->data['lead_info']['is_mobile'],
				],
				json_decode($this->data['integration_data'], true),
				json_decode($this->data['rule_integration_data'], true)
			)
		);

		if (app()->isLocal()) {

			$checked_status = 'success';
			$checked_substatus = '';
			$url = "http://api.affninja.app/finaro/order/create?checked_status={$checked_status}&checked_substatus={$checked_substatus}";

		} else {

			$url = "https://api.firano.ru/api/order/create";
		}

		$result = sendIntegrationRequest($url, 'POST', $params_string);

		$curl_info = $result['curl_info'];
		$response = json_decode($result['response'], true);

		// Запись лога
        $this->insertLog($url . '?' . $params_string, $response);

		if ($curl_info['http_code'] !== 200 || !isset($response['msg'])) {
            $this->releaseJob();
            return;
        }

		switch ($response['msg']) {
			case 'success':
				if (empty($response['order_id'])) {
					throw new IncorrectExternalKey('Finaro', $this->data['lead_info']['id']);
				}

				// Записуем external_key в таблицу лидов
				Lead::find($this->data['lead_info']['id'])->update([
					'integration_id' => $this->data['integration_id'],
					'external_key' => $response['order_id'],
					'is_integrated' => 1
				]);
				break;


			case 'double': // Дубль

				$lead = Lead::find($this->data['lead_info']['id']);

				// Пишем external_key лиду и идентификатор интеграции
				$lead->update([
					'integration_id' => $this->data['integration_id'],
					'external_key' => $lead->hash,
				]);

                // Отправляем лид в треш с причиной отмены "Дубль"
                $lead->tr($lead->id, Lead::DUBL_SUBSTATUS_ID);
                break;

			case 'error':

				switch ($response['error']) {
					case 'wrong_goods_id_param':
					case 'wrong_msisdn':
					case 'wrong_name':
					case 'wrong_api_key':
					case 'fraud':
					case 'msisdn_banned':
					case 'lead_price_not_defined':
					case 'unknown':
						break;

					case 'spam_protection':
						$this->release(60 * 60);
						break;
				}
		}
	}

	/**
	 * Запись лога
	 *
	 * @param $url
	 * @param $response
	 */
	private function insertLog($url, $response)
	{
		$log = "-----\n"
			. 'Date: ' . date('d.m.Y H:i:s') . "\n"
			. "Url: {$url}\n"
			. 'Response: ' . serialize($response)
			. "\n";

		\File::append(storage_path() . "/logs/finaro.log", $log);
	}
}
