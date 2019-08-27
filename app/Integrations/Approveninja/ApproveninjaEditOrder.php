<?php
declare(strict_types=1);

namespace App\Integrations\Approveninja;

use Illuminate\Bus\Queueable;
use LogicException;
use App\Models\Lead;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class ApproveninjaEditOrder implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;

    private $lead_id;
    private $lead_info;

    public function __construct(int $lead_id)
    {
        $this->lead_id = $lead_id;
    }

    public function handle(Lead $lead)
    {
        $this->lead_info = $lead->getById($this->lead_id, ['order', 'integration']);

        $url = $this->getRequestUrl();
        $result = sendIntegrationRequest($url);
        $response = json_decode($result['response'], true);

        $this->insertLog($url, $response);

        if ($response['status'] !== 'ok') {
            throw new LogicException('Cant edit order on ApproveNinja. Response: ' . print_r($response, true));
        }
    }

    private function getRequestUrl()
    {
        $params_string = http_build_query([
            'order_hash' => $this->lead_info['external_key'],
            'products' => $this->lead_info['order']['products'],
            'api_key' => $this->lead_info['integration']['integration_data_array']['api_key'],
            'remove_old_products' => '1',
            'format' => 'json'
        ]);

        return "http://api.approve.ninja/method/order.edit?{$params_string}";
    }

    /**
     * Запись лога
     *
     * @param $url
     * @param $response
     */
    private function insertLog($url, $response)
    {
        $log = "-----\n"
            . "Method: order.edit\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . "Url: {$url}\n"
            . 'Response: ' . serialize($response)
            . "\n";

        \File::append(storage_path('/logs/approveninja.log'), $log);
    }
}
