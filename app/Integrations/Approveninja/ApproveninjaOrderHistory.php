<?php
declare(strict_types=1);

namespace App\Integrations\Approveninja;

use App\Exceptions\Integration\BadResponse;
use App\Models\{
    Integration, Lead, LeadStatusLog
};
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ApproveninjaOrderHistory extends Command
{
    public const ALLOWABLE_SECONDS_TO_CHECK_ORDER = 3600;
    public const INTEGTATION_TITLE = 'Approveninja';
    public const SUBSTATUS_PAIRS = [
        15 => 30, // Звонок абоненту
        17 => 31, // Авт. звонок абоненту
        21 => 32, // В ожидании
        22 => 33, // Заказ подтвержден
        23 => 34, // Заказ отклонен
        24 => 35, // Переведен в статус Треш
        33 => 27, // Перезвонить позже
    ];

    protected $signature = 'approveninja:order.history';
    protected $description = 'Get order\'s history from approveninja integration.';

    private $lead;

    public function __construct(Lead $lead)
    {
        parent::__construct();

        $this->lead = $lead;
    }

    public function handle()
    {
        $integrations = (new Integration())->getActiveByTitle(self::INTEGTATION_TITLE);

        foreach ($integrations as $integration_info) {

            $result = sendIntegrationRequest($this->getRequestUrl($integration_info));

            $response = json_decode($result['response'], true);

            if (isset($response['status']) && $response['status'] === 'error') {
                throw new BadResponse(self::INTEGTATION_TITLE, $response);
            }

            foreach ($response AS $order) {

                $foreign_changed_at = Carbon::createFromTimestamp($order['date_add'])->toDateTimeString();

                try {

                    $lead = $this->lead->getByExternalKey($integration_info['id'], $order['order_hash']);

                    $last_status_log = LeadStatusLog::findLastForLead($lead);

                    if (is_null($last_status_log) || $foreign_changed_at !== $last_status_log['foreign_changed_at']) {

                        $lead->changeSubstatus(
                            $this->getSubstatusId((int)$order['type_id']),
                            '',
                            $foreign_changed_at
                        );
                    }

                } catch (ModelNotFoundException $e) {
                    continue;
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

        return "http://api.approve.ninja/method/order.history?{$params_string}";
    }

    private function getSubstatusId(int $type_id): int
    {
        if (!isset(self::SUBSTATUS_PAIRS[$type_id])) {
            throw new \LogicException();
        }
        return self::SUBSTATUS_PAIRS[$type_id];
    }

}