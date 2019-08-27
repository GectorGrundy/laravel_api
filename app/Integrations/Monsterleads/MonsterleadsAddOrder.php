<?php
declare(strict_types=1);

namespace App\Integrations\Monsterleads;

use App\Models\Lead;
use App\Models\Integration;
use Illuminate\Bus\Queueable;
use App\Integrations\ReleaseJob;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Exceptions\Integration\IncorrectExternalKey;

class MonsterleadsAddOrder implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
    use ReleaseJob;

    private const API_BASEPATH = 'http://api.monsterleads.pro/method';
    private const DUBL_ERROR = 438;

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

    public function handle()
    {
        $this->lead = Lead::findOrFail($this->lead_id);
        if ($this->lead->cannotIntegrate()) {
            return;
        }

        $this->integration = Integration::findOrFail($this->integration_id);

        $url = $this->getRequestUrl();

        $result = sendIntegrationRequest($url);
        $response = json_decode($result['response'], true);

        $this->insertLog($url, $response);

        if ($result['curl_info']['http_code'] !== 200 || $response === null || !isset($response['status'])) {
            $this->releaseJob();
            return;
        }

        if ($response['status'] === 'error') {
            return $this->processErrorResponse($response);
        }

        if (empty($response['lead_hash'])) {
            throw new IncorrectExternalKey('Monsterlaead', $this->lead_id);
        }

        $this->lead->setAsIntegrated($response['lead_hash']);
    }

    private function getRequestUrl(): string
    {
        return self::API_BASEPATH . '/order.add?'
            . http_build_query(array_merge([
                    'tel' => $this->lead->order['phone'],
                    'traffic_type' => 0,
                    'geo' => $this->lead->country['code'] ?? '',
                    'client' => $this->lead->order['name'],
                    'mail' => $this->lead->order['email'],
                    'comments' => $this->lead->order['comment'],
                    'ip' => $this->lead['ip'] ?? '',
                    'format' => 'json'
                ],
                    $this->integration['integration_data_array'],
                    $this->lead->target_geo_rule['integration_data_array']
                )
            );
    }

    private function processErrorResponse($response)
    {
        if ($this->isTrashedResponse($response)) {
            return $this->trashLead();
        }

        throw new \LogicException('Неожиданный ответ от Monsterleads. Response: ' . print_r($response, true));
    }

    private function isTrashedResponse(array $response): bool
    {
        return isset($response['error_code']) && (int)$response['error_code'] === self::DUBL_ERROR;
    }

    private function trashLead()
    {
        /**
         * @var Lead $lead
         */
        $lead = Lead::find($this->lead_id);

        $lead->setAsIntegrated($this->lead->generateExternalKeyById());
        $lead->trash(Lead::DUBL_SUBSTATUS_ID);
    }

    private function insertLog(string $url, $response)
    {
        $log = "-----\n"
            . "Method: order.add\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . "Url: {$url}\n"
            . 'Response: ' . serialize($response)
            . "\n";

        \File::append(storage_path('/logs/monsterleads.log'), $log);
    }
}
