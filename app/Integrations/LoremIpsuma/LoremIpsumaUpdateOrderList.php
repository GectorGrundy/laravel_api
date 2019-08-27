<?php
declare(strict_types=1);

namespace App\Integrations\LoremIpsuma;

use Hashids;
use Carbon\Carbon;
use App\Models\{
    Integration, Lead
};
use Illuminate\Console\Command;
use App\Exceptions\Integration\BadResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Docs http://tamyonetim.com/data/docs/Lorem-Ipsuma-CRM-API.pdf
 */
class LoremIpsumaUpdateOrderList extends Command
{
    public const INTEGTATION_TITLE = 'LoremIpsuma';
    public const API_ENDPOINT = 'http://tamyonetim.com/api.php';

    protected $signature = 'loremipsuma:update_order_list {type}';
    protected $description = 'Get orders from LoremIpsuma integration';

    public function handle()
    {
        $this->validateType();

        $integrations = (new Integration())->getActiveByTitle(self::INTEGTATION_TITLE);
        if (!$integrations->count()) {
            return;
        }

        foreach ($integrations as $integration) {

            $this->getLeadsQuery($integration)->chunk(75, function ($leads) use ($integration) {

                $params = $this->getRequestUrl($integration, $leads);
                $result = sendIntegrationRequest(self::API_ENDPOINT, 'POST', $params);

                $response = $this->parseResponse($result);

                if (isset($response['status']) && $response['status'] !== 'success') {
                    throw new BadResponse(self::INTEGTATION_TITLE, $response);
                }

                foreach ($response['data'] as $lead_hash => $order) {

                    if ($this->skipStatus($order['status'])) {
                        continue;
                    }

                    // $lead_hash may be hash or external_key of lead.
                    $decoded = Hashids::decode($lead_hash);
                    if (isset($decoded[0])) {
                        try {
                            $lead = Lead::findOrFail($decoded[0]);
                        } catch (ModelNotFoundException $e) {
                            $lead = (new Lead())->getByExternalKey($integration['id'], $lead_hash);
                        }
                    } else {
                        $lead = (new Lead())->getByExternalKey($integration['id'], $lead_hash);
                    }

                    try {
                        $this->processLead($lead, $order);
                    } catch (BadResponse $e) {
                        app('sentry')->captureException($e);
                    }
                }
            });
        }
    }

    private function parseResponse($result): array
    {
        if (!starts_with($result['response'], '{')) {
            $result['response'] = substr(
                $result['response'],
                strpos($result['response'], '{'),
                strlen($result['response']) - 1
            );
        }

        return json_decode($result['response'], true);
    }

    private function processLead(Lead $lead, array $response)
    {
        if ($response['status'] === 'unknown') {
            // 19) The status of the order is unknown, usually because there is
            // not such an order in the system, or that order does not belong to the requester referrer.
            throw new BadResponse(self::INTEGTATION_TITLE, [
                'lead_hash' => $lead['hash'], 'status' => $response['status']
            ]);
        }

        if ($response['conversion']) {

            switch ($response['status']) {
                case 'approved':
                case 'quality_control':
                case 'shipping_queue':
                case 'shipped':
                    $lead->approveIfNotAproved();
                    break;

                case 'delivered':
                    // 15) The order products have been delivered to its corresponding customer.
                    if ($lead['sub_status_id'] !== Lead::PURCHASED_SUBSTATUS_ID) {
                        $lead->approveIfNotAproved()->changeSubstatus(Lead::PURCHASED_SUBSTATUS_ID);
                    }
                    break;

                case 'undelivered':
                    // 16) The order products could not be delivered to its corresponding customer.
                    if ($lead['sub_status_id'] !== Lead::UNDELIVERED_SUBSTATUS_ID) {
                        $lead->approveIfNotAproved()->changeSubstatus(Lead::UNDELIVERED_SUBSTATUS_ID);
                    }
                    break;

                case 'rejected':
                case 'refund':
                    if ($lead['sub_status_id'] !== Lead::APPROVED_THEN_CANCELLED_SUBSTATUS_ID) {
                        $lead->approveIfNotAproved()->changeSubstatus(Lead::APPROVED_THEN_CANCELLED_SUBSTATUS_ID);
                    }
                    break;
            }
        } else {
            switch ($response['status']) {
                case 'trash':
                case 'invalid':
                case 'unreachable':
                    if (!$lead->isTrashed()) {
                        $lead->trash(Lead::INCORRECT_DATA_SUBSTATUS_ID);
                    }
                    break;

                case 'canceled':
                    if (!$lead->isCancelled()) {
                        $lead->cancel();
                    }
                    break;

                case 'call_later':
                    if ($lead['sub_status_id'] !== Lead::CALL_LATER_SUBSTATUS_ID) {
                        $lead->changeSubstatus(Lead::CALL_LATER_SUBSTATUS_ID);
                    }
                    break;
            }
        }
    }

    private function skipStatus(string $status)
    {
        return \in_array($status, ['waiting', 'process', 'lead', 'suspicious', 'called']);
    }

    private function getRequestUrl(Integration $integration, Collection $leads): array
    {
        $ids = [];
        foreach ($leads as &$lead) {
            if ($lead['lorem_ipsuma_version']) {
                $ids[] = $lead['external_key'];
            } else {
                $ids[] = $lead['hash'];
            }
        }
        unset($lead);

        return array_merge([
            'ref_lead_ids' => implode(',', $ids),
            'task' => 'enquiry',
            'api_language' => 'en-GB',
            'format' => 'json',
        ],
            json_decode($integration['integration_data'], true)
        );
    }

    private function getLeadsQuery($integration): Builder
    {
        $query = Lead::whereIntegration($integration)->whereIntegrated();

        switch ($this->argument('type')) {
            case '2_weeks':
                $two_weeks_ago = Carbon::now()->subDays(14)->toDateTimeString();
                return $query->createdFrom( $two_weeks_ago);

            case 'new':
                return $query->whereIn('status', [Lead::NEW, Lead::APPROVED])
                    ->whereNotIn('sub_status_id', [
                        Lead::PURCHASED_SUBSTATUS_ID,
                        Lead::UNDELIVERED_SUBSTATUS_ID,
                        Lead::APPROVED_THEN_CANCELLED_SUBSTATUS_ID
                    ]);
        }
    }

    private function validateType()
    {
        if (!\in_array($this->argument('type'), ['2_weeks', 'new'])) {
            throw new \BadMethodCallException('Unknown type.');
        }
    }
}