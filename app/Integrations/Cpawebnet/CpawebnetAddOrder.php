<?php
declare(strict_types=1);

namespace App\Integrations\Cpawebnet;

use App\Integrations\ReleaseJob;
use App\Models\{
    Country, Integration, Lead
};
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class CpawebnetAddOrder implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
    use ReleaseJob;

    public const CREATE_ORDER_URL = 'http://advertam.net/api.php';

    public const RANDOM_IPS = [
        '176.32.19.73',
        '46.231.33.30',
        '82.63.42.36',
        '5.90.72.14',
        '5.169.164.120'
    ];
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
        $this->lead = Lead::with(['target_geo_rule', 'country'])->findOrFail($this->lead_id);
        if ($this->lead->cannotIntegrate()) {
            return;
        }

        $this->integration = Integration::findOrFail($this->integration_id);

        $params_str = $this->getRequestParamsString();

        $result = sendIntegrationRequest(self::CREATE_ORDER_URL, 'POST', $params_str);

        $response = json_decode($result['response'], true);

        $this->insertLog($params_str, array_merge($response, ['http_code' => $result['curl_info']['http_code']]));

        if (!$response['success'] || $result['curl_info']['http_code'] !== 200) {
            $this->releaseJob();
            return;
        }

        $external_key = $this->lead->generateExternalKeyById();
        $this->lead->setAsIntegrated($external_key);
    }

    private function getRequestParamsString(): string
    {
        $click_id = (new Cpawebnet(
            (int)$this->lead->target_geo_rule['integration_data_array']['user_id'],
            (int)$this->lead->target_geo_rule['integration_data_array']['offer_id'],
            $this->lead['hash'],
            $this->lead['ip'],
            $this->lead['user_agent'],
            $this->lead->country['code']
        ))->run();

        return '?' . http_build_query(array_merge([
                    'ip' => $this->getIp(),
                    'name' => $this->lead->order['name'],
                    'phone' => $this->lead->order['phone'],
                    'clickid' => $click_id,
                ],
                    $this->integration['integration_data_array'],
                    $this->lead->target_geo_rule['integration_data_array']
                )
            );
    }

    private function getIp()
    {
        if ($this->lead['country_id'] === Country::IT && $this->lead['country_id'] !== $this->lead['ip_country_id']) {
            return self::RANDOM_IPS[array_rand(self::RANDOM_IPS)];
        }

        return $this->lead['ip'];
    }

    private function insertLog(string $params_str, array $response): void
    {
        $log = "-----\n"
            . "Method: order.add\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . 'Url: ' . self::CREATE_ORDER_URL . $params_str . "\n"
            . 'Response: ' . serialize($response)
            . "\n";

        \File::append(storage_path('/logs/cpawebnet.log'), $log);
    }
}
