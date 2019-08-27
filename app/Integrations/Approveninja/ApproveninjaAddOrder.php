<?php
declare(strict_types=1);

namespace App\Integrations\Approveninja;

use App\Integrations\ReleaseJob;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Exceptions\Integration\IncorrectExternalKey;

class ApproveninjaAddOrder implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
    use ReleaseJob;

    // Коды ошибок, присылаемые от Approveninja
    public const DUBL_ERROR = 419;
    public const INVALID_PARAMETER_ERROR = 403;

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

        $url = $this->getRequestUrl();

        $result = sendIntegrationRequest($url);
        $response = json_decode($result['response'], true);

        $this->insertLog($url, $response);

        if ($result['curl_info']['http_code'] !== 200 || $response === null || !isset($response['status'])) {
            $this->releaseJob();
            return;
        }

        if ($response['status'] === 'error') {
            if ($this->needLeadToTrash($response)) {
                $this->trashLead();
            }
        } else {
            if (empty($response['order_hash'])) {
                throw new IncorrectExternalKey('Approveninja', $this->lead['id']);
            }

            $this->lead->setAsIntegrated($response['order_hash']);
        }
    }

    private function getRequestUrl(): string
    {
        return 'http://api.approve.ninja/method/order.add?'
            . http_build_query(array_merge([
                    'phone' => $this->lead->order['phone'],
                    'client' => $this->lead->order['name'],
                    'address' => $this->lead->order['comment'],
                    'products' => $this->lead->order['products'],
                    'geo_code' => $this->lead->country['code'] ?? '',
                    'user_ip' => $this->lead['ip'] ?? '',
                    'user_agent' => $this->lead['user_agent'] ?? '',
                    'user_language' => $this->lead->locale['code'] ?? 'ru',
                    'valid_phone' => $this->lead['is_valid_phone'],
                    'source_publisher' => $this->lead->publisher['hash'],
                    'source_flow' => $this->lead->flow['hash'],
                    'messenger_id' => $this->getMessengerId($this->lead->order['contact_type']),
                    'format' => 'json'
                ],
                    $this->integration['integration_data_array'],
                    $this->lead->target_geo_rule['integration_data_array']
                )
            );
    }

    private function needLeadToTrash($response): bool
    {
        if (isset($response['error_code'])
            && ((int)$response['error_code'] === self::DUBL_ERROR
                || (int)$response['error_code'] === self::INVALID_PARAMETER_ERROR)
        ) {
            return true;
        }

        return false;
    }

    private function trashLead()
    {
        $lead = Lead::find($this->lead['id']);

        $lead->setAsIntegrated($this->lead->generateExternalKeyById());
        $lead->trash(Lead::DUBL_SUBSTATUS_ID);
    }

    private function insertLog($url, $response)
    {
        $log = "-----\n"
            . "Method:order.add\n"
            . 'Date:' . date('d.m.Y H:i:s') . "\n"
            . "Url:{$url}\n"
            . 'Response:' . serialize($response)
            . "\n";

        \File::append(storage_path('/logs/approveninja.log'), $log);
    }

    private function getMessengerId(string $contant_type)
    {
        switch ($contant_type) {
            case Order::WHATSAPP_CONTACT_TYPE:
                return 2;

            case Order::TELEGRAM_CONTACT_TYPE:
                return 3;

            case Order::MESSENGER_CONTACT_TYPE:
                return 4;

            case Order::VIBER_CONTACT_TYPE:
                return 5;

            case Order::LINE_CONTACT_TYPE:
                return 6;

            case Order::WECHAT_CONTACT_TYPE:
                return 7;

            case Order::CALL_CONTACT_TYPE:
            default:
                return 1;
        }
    }
}
