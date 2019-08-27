<?php
declare(strict_types=1);

namespace App\Integrations\Approveninja;

use App\Exceptions\Integration\BadResponse;
use App\Models\{
    Integration, Lead
};
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ApproveninjaUpdateOrderList extends Command
{
    public const ALLOWABLE_SECONDS_TO_CHECK_ORDER = 3600;
    public const INTEGTATION_TITLE = 'Approveninja';

    protected $signature = 'approveninja:update_order_list';
    protected $description = 'Get orders from approveninja integration which was updated';

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

            $url = $this->getRequestUrl($integration_info);
            $result = sendIntegrationRequest($url);

            $this->insertJsonLastErrorLog($result);

            $response = json_decode($result['response'], true);

            if (isset($response['status']) && $response['status'] === 'error') {
                throw new BadResponse(self::INTEGTATION_TITLE, $response);
            }

            // Получаем метку времени, начиная от которой нужно обрабатывать лиды
            $allowed_timestamp_to_checking_order = time() - self::ALLOWABLE_SECONDS_TO_CHECK_ORDER;

            foreach ($response AS $order) {

                // Если время изменения заказа меньше чем допустимая для проверки заказа - пропускаем его
                if ((int)$order['update_date_unix'] <= $allowed_timestamp_to_checking_order) {
                    continue;
                }

                try {
                    $lead = $this->lead->getByExternalKey($integration_info['id'], $order['order_hash']);
                } catch (ModelNotFoundException $e) {
                    continue;
                }

                if (config('integrations.approveninja.status_pairs.' . $order['status']) === Lead::APPROVED) {
                    if (!$lead->isApproved()) {
                        $lead->approve();
                    }

                } elseif (config('integrations.approveninja.status_pairs.' . $order['status']) === Lead::CANCELLED) {

                    $sub_status_id = $this->getSubstatusId($order['cancel_reason']);

                    if ($this->isTrashedSubstatus($order['cancel_reason'])) {

                        if (!$lead->isTrashed()) {
                            $lead->trash($sub_status_id);
                        }

                    } else {
                        if (!$lead->isCancelled()) {
                            $lead->cancel($sub_status_id);
                        }
                    }
                }
            }
        }
    }

    private function getRequestUrl($integration_info): string
    {
        $params_string = http_build_query(array_merge(
            ['itemppage' => 200],
            json_decode($integration_info['integration_data'], true)
        ));

        return "http://api.approve.ninja/method/order.listing?{$params_string}";
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

    private function isTrashedSubstatus($cancel_reason)
    {
        return in_array($cancel_reason, [1, 4, 5, 6, 10, 14, 18, 19]);
    }

    private function insertJsonLastErrorLog($result)
    {
        if (json_last_error()) {
            $log = 'json_last_error: ' . json_last_error()
                . '; json_last_error_msg: ' . json_last_error_msg()
                . '; result: ' . print_r($result, true);
            \File::append(storage_path('logs/approveninja.log'), $log);
        }
    }
}