<?php
declare(strict_types=1);

namespace App\Integrations\RocketProfit;

use App\Integrations\ReleaseJob;
use App\Models\{
    Integration, Lead
};
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class RocketProfitAddOrder implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
    use ReleaseJob;

    public const CREATE_ORDER_URL = 'https://tracker.rocketprofit.com/conversion/new';

    /**
     * @var Lead
     */
    private $lead;
    /**
     * @var Integration
     */
    private $integration;
    /**
     * @var int
     */
    private $lead_id;
    /**
     * @var int
     */
    private $integration_id;

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

        $this->integration = Integration::findOrFail($this->integration_id);

        $params_str = $this->getRequestParamsString();

        $result = sendIntegrationRequest(self::CREATE_ORDER_URL, 'POST', $params_str, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        $response = json_decode($result['response'], true);

        $this->insertLog($params_str, array_merge($response, ['http_code' => $result['curl_info']['http_code']]));

        if ($this->httpCodeBad($result['curl_info']['http_code'])) {
            $this->releaseJob();
            return;
        }

        $external_key = $this->lead->generateExternalKeyById();
        $this->lead->setAsIntegrated($external_key);
    }

    private function getRequestParamsString(): string
    {
        return '?' . http_build_query(array_merge([
                'ip' => $this->lead['ip'],
                'name' => $this->lead->order['name'],
                'phone' => $this->lead->order['phone'],
                'sid1' => $this->lead['hash'],
		        'country_code' => $this->lead->country['code']
            ],
                $this->integration['integration_data_array'],
                $this->lead->target_geo_rule['integration_data_array']
            )
        );
    }

    private function httpCodeBad($http_code): bool
    {
        return $http_code !== 200;
    }

    private function insertLog(string $params_str, array $response)
    {
        $log = "-----\n"
            . "Method: order.add\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . 'Url: ' . self::CREATE_ORDER_URL . $params_str . "\n"
            . 'Response: ' . serialize($response)
            . "\n";

        \File::append(storage_path('/logs/rocketprofit.log'), $log);
    }
}
