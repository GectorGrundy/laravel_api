<?php
declare(strict_types=1);

namespace App\Integrations\LoremIpsuma;

use App\Integrations\ReleaseJob;
use App\Models\Integration;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Docs http://tamyonetim.com/data/docs/Lorem-Ipsuma-CRM-API.pdf
 */
class LoremIpsumaAddOrder implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
    use ReleaseJob;

    public const API_ENDPOINT = 'http://tamyonetim.com/api.php';

    /**
     * @var Lead
     */
    private $lead;
    /**
     * @var Integration
     */
    private $integration;

    private $lead_id;
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

        $external_key = $this->lead->generateExternalKeyById();
        $params = $this->getRequestParams($external_key);

        $result = sendIntegrationRequest(self::API_ENDPOINT, 'POST', $params);

        if (!starts_with($result['response'], '{')) {
            $result['response'] = substr(
                $result['response'],
                strpos($result['response'], '{'),
                strlen($result['response']) - 1
            );
        }
        $response = json_decode($result['response'], true);

        $this->insertLog($params, $response);

        if ($response === null || !isset($response['status']) || $response['status'] === 'error') {
            $this->releaseJob();
            return;
        }

        $this->lead->setAsIntegrated($external_key);

        $this->lead->lorem_ipsuma_version = 1;
        $this->lead->save();
    }

    private function getRequestParams($external_key): array
    {
        $integration_data = $this->integration['integration_data_array'];

        if (!isset($integration_data['ref_key'])) {
            $integration_data['ref_key'] = str_random();

            $this->integration->update([
                'integration_data' => json_encode($integration_data)
            ]);
        }

        return array_merge([
            'full_name' => $this->lead->order['full_name'],
            'phone' => $this->lead->order['phone'],
            'ref_lead_id' => $external_key,
            'customer_note' => $this->lead->order['comment'],
            'task' => 'lead',
            'api_language' => 'en-GB',
            'format' => 'json',
        ],
            $integration_data,
            $this->lead->target_geo_rule['integration_data_array']
        );
    }

    private function insertLog($params, $response)
    {
        $log = "-----\n"
            . "Method:task.lead\n"
            . 'Date:' . date('d.m.Y H:i:s') . "\n"
            . "Url:" . self::API_ENDPOINT . '?' . http_build_query($params) . "\n"
            . 'Response:' . serialize($response)
            . "\n";

        \File::append(storage_path('/logs/loremipsuma.log'), $log);
    }
}
