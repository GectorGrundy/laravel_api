<?php
declare(strict_types=1);

namespace App\Integrations\Adcombo;

use App\Integrations\ReleaseJob;
use App\Models\Integration;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class AdcomboAddOrder implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
    use ReleaseJob;

    public const API_URL = 'https://api.adcombo.com/order/create/';

    /**
     * @var Lead
     */
    private $lead;
    private $lead_id;
    private $integration_id;
    private $integration;

    public function __construct(int $lead_id, int $integration_id)
    {
        $this->lead_id = $lead_id;
        $this->integration_id = $integration_id;
    }

    public function handle(): void
    {
        $this->lead = Lead::findOrFail($this->lead_id);
        if ($this->lead->cannotIntegrate()) {
            return;
        }

        // Adcombo не принимает лиды, у которых телефон - это слово
        if ((int)$this->lead->order['phone'] === 0) {
            $this->lead->setAsIntegrated($this->lead->generateExternalKeyById());
            $this->lead->trash(Lead::INCORRECT_DATA_SUBSTATUS_ID);
            return;
        }

        $this->integration = Integration::findOrFail($this->integration_id);

        $params = $this->getRequestParams();
        $result = sendIntegrationRequest(self::API_URL, 'POST', $params);

        $response = json_decode($result['response'], true);
        $this->insertLog($params, $response);

        if ($result['curl_info']['http_code'] !== 200 || (!isset($response['code']) || $response['code'] !== 'ok')) {
            $this->releaseJob();
            return;
        }

        $this->lead->setAsIntegrated($response['order_id']);
    }

    private function getRequestParams(): array
    {
        return [
            'api_key' => $this->integration->integration_data_array['api_key'],
            'name' => $this->lead->order->name,
            'phone' => $this->lead->order->phone,
            'offer_id' => $this->lead->target_geo_rule->integration_data_array['offer_id'],
            'country_code' => $this->lead->country->code,
            'price' => $this->lead->price,
            'base_url' => $this->lead->domain['domain'],
            'ip' => $this->lead->ip,
            'referrer' => $this->lead->referer,
            'quantity' => 1,
        ];
    }

    private function insertLog(array $request, $response)
    {
        $log = "-----\n"
            . "Method: order.add\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . 'Request: ' . self::API_URL . '?' . http_build_query($request) . "\n"
            . 'Response: ' . json_encode($response) . "\n"
            . "\n";

        \File::append(storage_path('/logs/adcombo.log'), $log);
    }
}
