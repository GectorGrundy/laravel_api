<?php
declare(strict_types=1);

namespace App\Integrations\Fetchr;

use Carbon\Carbon;
use Log;
use App\Models\{
    Integration, Lead
};
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FetchrUpdateOrderList extends Command
{
    public const ALLOWABLE_SECONDS_TO_CHECK_ORDER = 3600;
    public const INTEGTATION_TITLE = 'Fetchr';
    public const API_ENDPOINT = 'https://business.fetchr.us/api/client/awb';

    protected $signature = 'fetchr:update_order_list';
    protected $description = 'Get orders from fetchr integration which was updated';

    private $integration;
    private $lead;

    public function __construct(Integration $integration, Lead $lead)
    {
        parent::__construct();

        $this->integration = $integration;
        $this->lead = $lead;
    }

    public function handle()
    {
        $integrations = $this->integration->getActiveByTitle(self::INTEGTATION_TITLE);

        foreach ($integrations as $integration_info) {

            $params = $this->getRequestParams();
            $result = $this->getOrders($params, $integration_info);

            $response = json_decode($result, true);

            $this->insertLog($params, $response);

            if (isset($response['success']) && $response['status'] !== 'success') {
                Log::error('Fetchr возвращает ошибку в методе получения заказов', [
                        'integration_id' => $integration_info['id']]
                );
                return false;
            }

//            foreach ($response['data'] AS $order) {
               // process order
//            }
        }
    }

    private function getOrders(array $params, Integration $integration)
    {
        $ch = curl_init(self::API_ENDPOINT);

        $data = json_encode($params);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'authorization:' . $integration['integration_data_array']['authorization'],
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)]
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    private function getRequestParams(): array
    {
        $end_date = Carbon::create();
        $start_date = clone $end_date;
        $start_date = $start_date->subDays(2)->toDateTimeString();
        $end_date = $end_date->toDateTimeString();

        return [
            'format' => 'json',
            'type' => 'standard', //standard, mini, label, label6x4, label8x4,
            'search_value' => [],
            'search_key' => 'date',
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];

    }

    /**
     * Получаем идентификатор нашего субстатуса на основании присланного от approveninja
     *
     * @param $status
     * @return int|mixed
     */
    private function getSubstatusId($status)
    {
        $sub_status_id = 0;
        if (null !== config('integrations.approveninja.substatus_pairs.' . $status)) {
            $sub_status_id = config('integrations.approveninja.substatus_pairs.' . $status);
        }

        return $sub_status_id;
    }

    private function insertLog(array $params, $response)
    {
        $log = "-----\n"
            . "Method: order.listing\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . 'Url: ' . self::API_ENDPOINT . '?' . http_build_query($params) . "\n"
            . 'Response: ' . serialize($response)
            . "\n";

        \File::append(storage_path('/logs/fetchr.log'), $log);
    }
}
