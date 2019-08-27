<?php
declare(strict_types=1);

namespace App\Integrations\Affbay;

use App\Integrations\ReleaseJob;
use App\Models\Lead;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class AffbayAddOrder implements ShouldQueue
{
    use Queueable;
    use InteractsWithQueue;
    use SerializesModels;
    use ReleaseJob;

    public const LEAD_PATH = '/api/make/contact';

    /**
     * @var Lead
     */
    private $lead;
    private $lead_id;
    private $integration_id;
    private $api_host;

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

        if ($this->lead->target->isAutoapprove()) {
            $this->prepareAutoapproveLead();
            return;
        }

        $this->integrateLead();
    }

    private function prepareAutoapproveLead()
    {
        if (!$this->lead->is_valid_phone) {
            $this->lead->setAsIntegrated($this->lead->generateExternalKeyById());
            return $this->lead->trash(Lead::INCORRECT_DATA_SUBSTATUS_ID);
        }

        if (!Order::isPhoneDailyUnique($this->lead->order['phone'], $this->lead->order['id'])) {
            $this->lead->setAsIntegrated($this->lead->generateExternalKeyById());
            return $this->lead->trash(Lead::DUBL_SUBSTATUS_ID);
        }

        $this->integrateLead();
    }

    private function integrateLead()
    {
        $this->api_host = $this->lead->target_geo_rule['integration_data_array']['api_host'];

        $params = $this->getRequestParams();

        $response = sendIntegrationRequest($this->api_host . self::LEAD_PATH, 'POST', $params);

        $bom = pack('H*', 'EFBBBF');
        $response = json_decode(preg_replace("/^$bom/", '', $response['response']), true);

        $this->insertLog($params, $response);

        if (!isset($response['status'])) {
            throw new \LogicException('Error. Response: ' . serialize($response));
        }

        if (!isset($response['id'])) {
            $this->trashLead($response);

        } else {
            $external_key = $response['id'];
            $this->lead->setAsIntegrated($external_key);

            if ($this->lead->target->isAutoapprove()) {
                $this->lead->approve();
            }
        }
    }

    private function trashLead($response)
    {
        if (!\in_array($response['status'], ['double phone', 'invalid phone format'])) {
            throw new \LogicException('Error. Response: ' . serialize($response));
        }

        $this->lead->setAsIntegrated($this->lead->generateExternalKeyById());

        if ($response['status'] === 'double phone') {
            return $this->lead->trash(Lead::DUBL_SUBSTATUS_ID);
        }

        if ($response['status'] === 'invalid phone format') {
            return $this->lead->trash(Lead::INCORRECT_DATA_SUBSTATUS_ID);
        }
    }

    private function getRequestParams(): array
    {
        $name = trim($this->lead->order['name']);
        $last_name = (strpos($name, ' ') === false) ? '_' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim(preg_replace('#' . $last_name . '#', '', $name));

        return [
            'first_name' => empty($first_name) ? $last_name : $first_name,
            'last_name' => $last_name,
            'phone' => $this->lead->order['phone'],
            'click_id' => $this->lead['hash'],
            'product' => $this->lead->target_geo_rule['integration_data_array']['product'],
            'token' => $this->lead->target_geo_rule['integration_data_array']['token']
        ];
    }

    private function insertLog($request, $response)
    {
        $log = "-----\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . 'HOST: ' . $this->api_host . self::LEAD_PATH . "\n"
            . 'Request: ' . serialize($request) . "\n"
            . 'Response: ' . serialize($response) . "\n"
            . "\n";

        \File::append(storage_path('/logs/affbay.log'), $log);
    }
}
