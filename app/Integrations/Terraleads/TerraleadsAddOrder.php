<?php
declare(strict_types=1);

namespace App\Integrations\Terraleads;

use App\Integrations\ReleaseJob;
use App\Models\Integration;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Exceptions\Integration\BadResponse;

class TerraleadsAddOrder implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
    use ReleaseJob;

    public const CREATE_ORDER_URL = 'http://tl-api.com/api/lead/create';
    /**
     * @var Lead
     */
    private $lead;
    /**
     * @var int
     */
    private $lead_id;
    /**
     * @var int
     */
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

        $this->integration = Integration::findOrFail($this->integration_id);

        try {
            $api_connector = new CApiConnector();
            $api_connector->config['api_key'] = $this->integration['integration_data_array']['api_key'];
            $api_connector->config['offer_id'] = $this->lead->target_geo_rule['integration_data_array']['offer_id'];
            $api_connector->config['user_id'] = $this->lead->target_geo_rule['integration_data_array']['user_id'];

            $params = [
                'name' => $this->lead->order['name'],
                'phone' => $this->lead->order['phone'],
                'country' => $this->lead->country['code'],
                'sub_id' => $this->lead['hash'],
            ];
            $lead = $api_connector->create($params);

            $this->insertLog($lead, $params);

            if ($this->responseHaveBadStructure($lead)) {
                throw new BadResponse($this->integration->title, $lead);
            }

            $this->lead->setAsIntegrated($lead->id);

            switch (config("integrations.terraleads.status_pairs.{$lead->status}")) {
                case 'trashed':
                    $this->lead->trash();
                    break;

                case 'cancelled':
                    $this->lead->cancel();
                    break;
            }
        } catch (\Exception $e) {
            $this->insertLog([$e->getMessage()], $params);
            $this->releaseJob();
            return;
        }
    }

    private function responseHaveBadStructure($lead): bool
    {
        return !isset($lead->id, $lead->status) || $lead->status === 'error';
    }

    private function insertLog($response, $request)
    {
        $log = "-----\n"
            . "Method: order.add\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . 'Request: ' . serialize($request) . "\n"
            . 'Response: ' . serialize($response) . "\n";

        \File::append(storage_path('/logs/terraleads.log'), $log);
    }
}
