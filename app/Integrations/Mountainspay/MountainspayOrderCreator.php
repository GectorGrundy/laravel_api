<?php
declare(strict_types=1);

namespace App\Integrations\Mountainspay;

use App\Integrations\ReleaseJob;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * @deprecated
 */
class MountainspayOrderCreator implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
	use DispatchesJobs;
	use ReleaseJob;

	private $data;

	public function __construct($data)
	{
		$this->data = $data;
	}

	public function handle()
	{
		$integration_data = json_decode($this->data['integration_data'], true);
		$rule_integration_data = json_decode($this->data['rule_integration_data'], true);

		$params_string = http_build_query(
			array_merge(
				[
					'address' => '',
					'fio' => $this->data['lead_info']['order']['name'],
					'phone' => $this->data['lead_info']['order']['phone'],
					'price' => $this->data['lead_info']['price'],
					'total' => $this->data['lead_info']['price'],
					'ip' => $this->data['lead_info']['ip'],
					'domain' => 'http://' . $this->data['lead_exchange_data']['HTTP_HOST'],
					'webId' => $this->getWebId($this->data['integration_id'], $this->data['lead_info']['flow_id']),
					'externalId' => $this->getExternalId($this->data['lead_info']['id'], $this->data['integration_id']),
					'is_mobile' => $this->data['lead_info']['is_mobile']
				],
				$integration_data,
				$rule_integration_data
			)
		);

		$url = "http://mountainspay.com/api/v1/add-order";

		$result = sendIntegrationRequest($url, 'POST', $params_string, [
			'Content-Type: application/x-www-form-urlencoded'
		]);
		$response = json_decode($result['response'], true);

        $this->insertLog($url . '?' . $params_string, $response);

		if (isset($response['OK'])) {

			// Записуем external_key в таблицу лидов
			Lead::find($this->data['lead_info']['id'])->update([
				'integration_id' => $this->data['integration_id'],
				'external_key' => $response['OK'],
				'is_integrated' => 1
			]);

			if ($this->isSendSms($rule_integration_data['language'])) {
				$this->sendSms($this->data['lead_info']['order']['phone']);
			}

		} else {
            $this->releaseJob();
            return;
        }
	}

	/**
	 * Получение параметра "webId"
	 *
	 * @param $integration_id
	 * @param $flow_id
	 * @return string
	 */
	private function getWebId($integration_id, $flow_id)
	{
		return $integration_id . $flow_id;
	}

	/**
	 *     * Получение параметра "externalId"
	 *
	 * @param $lead_id
	 * @param $integration_id
	 * @return string
	 */
	private function getExternalId($lead_id, $integration_id)
	{
		return $lead_id . $integration_id;
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
			. "Date: " . date('d.m.Y H:i:s') . "\n"
			. "Url: {$url}\n"
			. "Response: " . serialize($response)
			. "\n";

		\File::append(storage_path() . "/logs/mountainspay.log", $log);
	}

	/**
	 * @param $language
	 * @return bool
	 */
	private function isSendSms($language)
	{
		return $language === 'de';
	}

	/**
	 *
	 * @param $phone
	 */
	private function sendSms($phone)
	{
		$api_key = 'we_dont_have_it';
		$ch = curl_init('http://alphasms.ua/api/xml.php');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$xml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<package key="{$api_key}">
<message>
<msg recipient= "{$phone}" sender="onlineshop" type="0">Herzlichen Dank fuer Ihre Entscheidung fuer "DropsOff"! 
Ihr "DropsOff" Berater wird Sie von der Tel.Nummer +4930255558604 anrufen.</msg>
</message>
</package>
XML;

		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: text/xml; charset=utf-8',
			'Content-Length: ' . strlen($xml)
		]);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$response = curl_exec($ch);
		curl_close($ch);

		$log = "-----------------------------\n"
			. "Date: " . date('d.m.Y H:i:s') . "\n"
			. "Request:\n" . $xml . "\n"
			. "~~~~~~~~~~\n"
			. "Response:\n" . $response
			. "\n";

		\File::append(storage_path() . "/logs/sms.log", $log);
	}
}
