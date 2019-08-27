<?php
declare(strict_types=1);

namespace App\Integrations\Leadrock;

use App\Exceptions\Integration\BadResponse;
use App\Models\{
    Integration, Lead
};
use Illuminate\Bus\Queueable;
use App\Integrations\ReleaseJob;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class LeadrockAddOrder implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
    use ReleaseJob;

    public const CREATE_ORDER_URL = 'http://leadrock.com/api/lead/create';

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

        $params = $this->getRequestParams();

        $result = sendIntegrationRequest(self::CREATE_ORDER_URL, 'POST', $params);
        $response = json_decode($result['response'], true);

        $http_code = $result['curl_info']['http_code'];

        $this->insertLog($params, array_merge($response, ['http_code' => $http_code]));

        if ($response['is_error'] || !isset($response['data']['id'])) {
            throw new BadResponse('Leadrock', $response);
        }

        if ($this->httpCodeBad($http_code)) {
            $this->releaseJob();
            return;
        }

        $this->lead->setAsIntegrated($response['data']['id']);
    }

    private function getRequestParams(): array
    {
        return array_merge([
            'user_name' => $this->lead->order['name'],
            'user_phone' => $this->lead->order['phone'],
            'track_id' => $this->lead['hash']
        ],
            $this->integration['integration_data_array'],
            $this->lead->target_geo_rule['integration_data_array']
        );
    }

    private function httpCodeBad($http_code): bool
    {
        return $http_code !== 200;
    }

    private function insertLog(array $params, array $response)
    {
        $log = "-----\n"
            . "Method: order.add\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . 'Url: ' . self::CREATE_ORDER_URL . '?' . http_build_query($params) . "\n"
            . 'Response: ' . serialize($response)
            . "\n";

        \File::append(storage_path('/logs/leadrock.log'), $log);
    }
}
