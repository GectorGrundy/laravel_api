<?php
declare(strict_types=1);

namespace App\Integrations\Monsterleads;

use Carbon\Carbon;
use App\Models\{
    Integration, Lead
};
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\Integration\BadResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MonsterleadsUpdateOrderList extends Command
{
    private const API_BASEPATH = 'http://api.monsterleads.pro/method';

    private const NEW_TYPE = 'new';
    private const WEEK_TYPE = 'week';
    private const ITEMPAGE = 50;

    public const INTEGTATION_TITLE = 'Monsterleads';

    protected $signature = 'monsterleads:update_order_list {type}';
    protected $description = 'Get orders from Monsterleads and update statuses';

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
        $this->validateType();

        $integrations = $this->integration->getActiveByTitle(self::INTEGTATION_TITLE);

        foreach ($integrations as $integration) {

            $this->getLeadsQuery($integration)->chunk(self::ITEMPAGE, function ($leads) use ($integration) {

                $url = $this->getRequestUrl($integration, $leads);
                $response = sendIntegrationRequest($url);

                $orders = $this->getLeadsFromResponse($response);
                $this->processLeads($orders, $integration);
            });
        }
    }

    private function getLeadsQuery($integration): Builder
    {
        $query = Lead::whereIntegration($integration)->whereIntegrated();

        switch ($this->argument('type')) {
            case self::NEW_TYPE:
                return $query->where('status', [Lead::NEW]);


            case self::WEEK_TYPE:
                $week_ago = Carbon::now()->subDays(7)->toDateTimeString();
                return $query->createdFrom($week_ago);
        }
    }

    private function getRequestUrl($integration, Collection $leads): string
    {
        return self::API_BASEPATH . '/lead.list?'
            . http_build_query(array_merge([
                'lead_list' => implode(',', $leads->pluck('external_key')->toArray()),
                'search_by' => 'hash',
                'format' => 'json',
            ],
                $integration['integration_data_array']
            ));
    }

    private function getLeadsFromResponse(array $result): iterable
    {
        $this->insertJsonLastErrorLog($result);

        $response = json_decode($result['response'], true);

        if (isset($response['status']) && $response['status'] === 'error') {
            throw new BadResponse(self::INTEGTATION_TITLE, $response);
        }
        return $response;
    }

    private function processLeads(iterable $orders, Integration $integration)
    {
        foreach ($orders AS $order) {

            try {
                $lead = $this->lead->getByExternalKey($integration['id'], $order['hash']);
            } catch (ModelNotFoundException $e) {
                continue;
            }

            if ($order['status_name'] === Lead::APPROVED) {
                $lead->approveIfNotAproved();

            } elseif ($order['status_name'] === 'aborted') {

                $cancel_reason = (int)$order['cancel_reason'];
                $sub_status_id = $this->getSubstatusId($cancel_reason);

                if ($this->isTrashedSubstatus($cancel_reason)) {
                    $lead->trashIfNotTrashed($sub_status_id);
                } else {
                    $lead->cancelIfNotCancelled($sub_status_id);
                }
            }
        }
    }

    /**
     * Получаем идентификатор нашего субстатуса на основании присланного от monsterleads
     *
     * @param int $status
     * @return int
     */
    private function getSubstatusId(int $status): int
    {
        $sub_status_id = 0;
        if (null !== config('integrations.monsterleads.substatus_pairs.' . $status)) {
            $sub_status_id = config('integrations.monsterleads.substatus_pairs.' . $status);
        }

        return $sub_status_id;
    }

    private function isTrashedSubstatus(int $cancel_reason): bool
    {
        return \in_array($cancel_reason, config('integrations.monsterleads.trashed_cancel_reasons'), true);
    }

    private function insertJsonLastErrorLog($result)
    {
        if (json_last_error()) {
            $log = 'json_last_error: ' . json_last_error()
                . '; json_last_error_msg: ' . json_last_error_msg()
                . '; result: ' . print_r($result, true);
            \File::append(storage_path('logs/monsterleads.log'), $log);
        }
    }

    private function validateType()
    {
        if (!\in_array($this->argument('type'), [self::NEW_TYPE, self::WEEK_TYPE])) {
            throw new \BadMethodCallException('Unknown type.');
        }
    }
}
