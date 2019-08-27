<?php
declare(strict_types=1);

namespace App\Integrations\LoremIpsuma;

use Illuminate\Console\Command;
use App\Models\Lead;
use Illuminate\Support\Collection;

/**
 * Docs http://tamyonetim.com/data/docs/Lorem-Ipsuma-CRM-API.pdf
 * case 'waiting':
 * // 1) The lead is still waiting for processing.
 *
 * case 'trash':
 * // 2) The lead is not a valid lead usually because of its invalid customer name or phone number,
 * // or because it is a repeated data.
 * Lead::find($lead_id)->trash(Lead::INCORRECT_DATA_SUBSTATUS_ID);
 *
 * case 'process':
 * // 3) The processing of the lead has just been started.
 *
 * case 'lead':
 * // 4) The lead is considered as a normal lead and can be called by the agents.
 *
 * case 'invalid':
 * // 5) The lead is considered as an invalid lead and may not be called by the agents.
 *
 * case 'unreachable':
 * // 6) The lead phone number was called, but it is unreachable.
 * Lead::find($lead_id)->trash(Lead::INCORRECT_DATA_SUBSTATUS_ID);
 *
 * case 'canceled':
 * // 7) The lead phone number was called, but the customer canceled their order.
 * Lead::find($lead_id)->cancel();
 *
 * case 'suspicious':
 * // 8) The order is considered as suspicious, and has to be investigated more.
 *
 * case 'call_later':
 * // 9) The customer was called, but either the customer or line is busy,
 * // or the customer did not answer, or has asked for calling them in a later time.
 * Lead::find($lead_id)->changeSubstatus(Lead::CALL_LATER_SUBSTATUS_ID);
 *
 * case 'called':
 * // 10) The order’s customer was called, but the result has not still been specified by the agent.
 *
 * case 'approved':
 * // 11) The agent has approved the order because the order’s customer has approved their order.
 *
 * case 'quality_control':
 * // 12) The agent has approved the order, and the order is in the quality control process.
 *
 * case 'shipping_queue':
 * // 13) The quality of the order has been approved,
 * // and the order is waiting in the shipping queue that its items to be shipped
 * // to its corresponding customer.
 * $lead = Lead::find($lead_id)->approveIfNotAproved();
 *
 *
 * case 'shipped':
 * // 14) The shipping process has been started for the order.
 *
 * case 'delivered':
 * // 15) The order products have been delivered to its corresponding customer.
 * Lead::find($lead_id)->approveIfNotAproved()->changeSubstatus(Lead::PURCHASED_SUBSTATUS_ID);
 *
 * case 'undelivered':
 * // 16) The order products could not be delivered to its corresponding customer.
 * Lead::find($lead_id)
 * ->approveIfNotAproved()
 * ->changeSubstatus(Lead::UNDELIVERED_SUBSTATUS_ID);
 *
 * case 'rejected':
 * // 17 )The order products were rejected by its corresponding customer in the delivery time.
 * Lead::find($lead_id)
 * ->approveIfNotAproved()
 * ->changeSubstatus(Lead::APPROVED_THEN_CANCELLED_SUBSTATUS_ID);
 *
 * case 'refund':
 * // 18) The customer has asked for the refund, and the order has been refunded.
 * Lead::find($lead_id)
 * ->approveIfNotAproved()
 * ->changeSubstatus(Lead::APPROVED_THEN_CANCELLED_SUBSTATUS_ID);
 *
 * case 'unknown':
 */
class LoremIpsumaValidateOrders extends Command
{
    public const INTEGTATION_TITLE = 'LoremIpsuma';
    public const API_ENDPOINT = 'http://tamyonetim.com/api.php';

    protected $signature = 'loremipsuma:validate';
    protected $description = '';

    public function handle()
    {
        if (app()->environment('production')) {
            $this->error('Cant run this command in production.');
            return;
        }

        $raw_leads = include(public_path('leads.php'));

        $chunks = collect($raw_leads)->chunk(100);

        $different_status_leads = [];
        $unknown_status_leads = [];

        $progress = $this->output->createProgressBar(\count($raw_leads));

        foreach ($chunks as &$leads) {
            $leads = collect($leads);

            $params = $this->getRequestUrl($leads);

            $result = sendIntegrationRequest(self::API_ENDPOINT, 'POST', $params);

            $response = $this->parseResponse($result);

            foreach ($response['data'] as $lead_hash => $order) {

                if (\is_numeric($lead_hash)) {
                    $lead = $leads->where('external_key', $lead_hash)->first();
                    $id = $lead['external_key'];
                } else {
                    $lead = $leads->where('hash', $lead_hash)->first();
                    $id = $lead['hash'];
                }

                try {
                    $status = $this->getOurLeadStatus($order);

                    if ($lead['status'] !== $status) {
                        $different_status_leads[] = $id;
                    }
                } catch (\LogicException $e) {
                    $unknown_status_leads[] = $id;
                }
                $progress->advance();
            }
        }

        unset($leads);

        $content = "Разные статусы: \n";
        $content .= implode(',', $different_status_leads);
        $content .= " \nНе найдено: \n";
        $content .= implode(',', $unknown_status_leads);

        \File::put(public_path('output'), $content);

        $progress->finish();
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

    private function getRequestUrl(Collection $leads): array
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

        return [
            'ref_lead_ids' => implode(',', $ids),
            'task' => 'enquiry',
            'api_language' => 'en-GB',
            'format' => 'json',

            'api_key' => 'vhG54JdTYfcv87298DdG',
            'ref_key' => 'b1dlgRBLbHFHknyy',
            'ref_address' => 'affninja.com',
        ];
    }

    private function getOurLeadStatus(array $order)
    {
        if ($order['status'] === 'unknown') {
            throw new \LogicException();
        }

        if (\in_array($order['status'], ['waiting', 'process', 'lead', 'suspicious', 'call_later', 'called'])) {
            return Lead::NEW;
        }

        if (\in_array($order['status'], ['trash', 'unreachable', 'invalid'])) {
            return Lead::TRASHED;
        }

        if ($order['status'] === 'canceled') {
            return Lead::CANCELLED;
        }

        if (\in_array($order['status'], [
            'approved', 'quality_control', 'shipping_queue', 'shipped', 'delivered', 'undelivered', 'rejected', 'refund'
        ])) {
            if ($order['conversion']) {
                return Lead::APPROVED;
            }
            return Lead::NEW;
        }
    }
}