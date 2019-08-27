<?php
declare(strict_types=1);

namespace App\Integrations\Leadbit;

use App\Integrations\ReleaseJob;
use App\Models\Integration;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class LeadbitAddOrder implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
    use ReleaseJob;

    /**
     * @var Lead
     */
    private $lead;
    /**
     * @var Integration
     */
    private $integration;

    public const INVALID_PARAMETER_ERROR = 403;
    public const DUBL_ERROR = 438;
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

        $url = $this->getRequestUrl();
        $request_params = $this->getRequestParams();

        $result = sendIntegrationRequest($url, 'POST', $request_params, [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);

        $response = json_decode($result['response'], true);

        $this->insertLog($url, $request_params, $response);

        if ($this->badHttStatus($result['curl_info']['http_code']) || $response['status'] !== 'success') {
            $this->releaseJob();
            return;
        }

        $external_key = $this->lead->generateExternalKeyById();
        $this->lead->setAsIntegrated($external_key);
    }

    private function getRequestParams(): array
    {
        return array_merge([
            'name' => $this->lead->order['name'],
            'phone' => $this->lead->order['phone'],
            'country' => $this->lead->country['code'],
            'sub1' => $this->lead['hash'],
            'referrer' => $this->lead->referrer
        ],
            $this->lead->target_geo_rule['integration_data_array']
        );
    }

    private function getRequestUrl(): string
    {
        return 'http://leadbit.com/api/new-order/' . $this->integration['integration_data_array']['api_key'];
    }

    private function badHttStatus($http_code): bool
    {
        return $http_code !== 200;
    }

    private function insertLog(string $url, array $request_params, $response)
    {
        $log = "-----\n"
            . "Method: order.add\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . "Url: {$url}\n"
            . 'Request: ' . serialize($request_params) . "\n"
            . 'Response: ' . serialize($response)
            . "\n";

        \File::append(storage_path('logs/leadbit.log'), $log);
    }
}
