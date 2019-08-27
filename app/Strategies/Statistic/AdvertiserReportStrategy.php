<?php
declare(strict_types=1);

namespace App\Strategies\Statistic;

use Auth;
use App\Models\Currency;
use App\Strategies\Statistic\AbstractStatisticStrategy;
use App\Classes\Statistics;
use App\Models\Lead;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Payload;

class AdvertiserReportStrategy implements AbstractStatisticStrategy
{
    public const OFFER = 'offer';
    public const PUBLISHER = 'publisher';
    public const DATE = 'date';
    public const TARGET = 'target';
    public const TARGET_GEO = 'target_geo';
    /**
     * @var Request
     */
    private $request;
    /**
     * @var string
     */
    private $group_field;

    public function get(Request $request, string $group_field)
    {
        $this->request = $request;
        $this->group_field = $group_field;

        $targets = $request->filled('target_hash') ? [$request->get('target_hash')] : [];

        $query = Lead::with($this->getWith())
            ->where('advertiser_id', Auth::user()->id)
            ->whereOffer($request->get('offer_hashes', []))
            ->whereTargets($targets)
            ->whereCountries($request->get('country_ids', []))
            ->whereAdvertiserCurrencies($request->get('currency_ids', []))
            ->search($request->get('search_field'), $request->get('search'));

        if ($request->group_by === 'processed_at') {
            $query->processedBetweenDates($request->input('date_from'), $request->input('date_to'));
        } else {
            $query->createdBetweenDates($request->input('date_from'), $request->input('date_to'));
        }

        $leads = $query->get();

        $result = [
            'total' => $this->getEmptyReportFields(),
            'stats' => [],
            'existing_currencies' => [],
        ];

        foreach ($leads as &$lead) {

            $value = $this->getValueByGroupField($lead);

            if (!isset($result['stats'][$value])) {
                $result['stats'][$value] = $this->getEmptyReportFields();
                $result['stats'][$value]['id'] = $value;
                $result['stats'][$value]['type'] = $group_field;

                $this->addAdditionalDataToField($result['stats'][$value], $lead);
            }

            $result['stats'][$value]['total_count'] += 1;
            $result['total']['total_count'] += 1;
            $payout = (float)$lead['advertiser_payout'];

            if (!\in_array($lead['advertiser_currency_id'], $result['existing_currencies'])) {
                $result['existing_currencies'][] = $lead['advertiser_currency_id'];
            }

            switch ($lead['status']) {
                case Lead::APPROVED;
                    $result['stats'][$value]['approved_count'] += 1;
                    $result['total']['approved_count'] += 1;

                    $currency_field = $lead->advertiser_currency['code'] . '_approved_payout';

                    if (!isset($result['stats'][$value][$currency_field])) {
                        $result['stats'][$value][$currency_field] = 0;
                    }

                    if (!isset($result['total'][$currency_field])) {
                        $result['total'][$currency_field] = 0;
                    }

                    $result['stats'][$value][$currency_field] += $payout;
                    $result['total'][$currency_field] += $payout;
                    break;

                case Lead::NEW;
                    $result['stats'][$value]['held_count'] += 1;
                    $result['total']['held_count'] += 1;

                    $currency_field = $lead->advertiser_currency['code'] . '_held_payout';

                    if (!isset($result['stats'][$value][$currency_field])) {
                        $result['stats'][$value][$currency_field] = 0;
                    }

                    if (!isset($result['total'][$currency_field])) {
                        $result['total'][$currency_field] = 0;
                    }

                    $result['stats'][$value][$currency_field] += $payout;
                    $result['total'][$currency_field] += $payout;
                    break;

                case Lead::CANCELLED;
                    $result['stats'][$value]['cancelled_count'] += 1;
                    $result['total']['cancelled_count'] += 1;
                    break;

                case Lead::TRASHED;
                    $result['stats'][$value]['trashed_count'] += 1;
                    $result['total']['trashed_count'] += 1;
                    break;
            }
        }

        // Calc approve
        foreach ($result['stats'] as &$group) {
            $group['real_approve'] = Statistics::calculateRealApprove(
                $group['approved_count'],
                $group['cancelled_count'],
                $group['held_count'],
                true
            );
            $group['approve'] = Statistics::calculateApprove(
                $group['approved_count'],
                $group['cancelled_count'],
                $group['held_count'],
                $group['trashed_count'],
                true
            );
        }

        $stats = collect($result['stats']);

        if ($request->get('sorting', 'asc') === 'asc') {
            $result['stats'] = $stats->sortBy($request->get('sort_by', 'id'))->values()->toArray();
        } else {
            $result['stats'] = $stats->sortByDesc($request->get('sort_by', 'id'))->values()->toArray();
        }

        $result['total']['real_approve'] = Statistics::calculateRealApprove(
            $result['total']['approved_count'],
            $result['total']['cancelled_count'],
            $result['total']['held_count']
        );

        $result['total']['approve'] = Statistics::calculateApprove(
            $result['total']['approved_count'],
            $result['total']['cancelled_count'],
            $result['total']['held_count'],
            $result['total']['trashed_count']
        );

        unset($lead, $stat);

        return $result;
    }

    private function getValueByGroupField(Lead $lead)
    {
        switch ($this->group_field) {
            case self::DATE:
                if ($this->request->input('group_by') === 'processed_at') {
                    return toDate($lead['processed_at']);
                }
                return toDate($lead['created_at']);

            case self::PUBLISHER:
                return $lead->publisher['hash'];

            case self::OFFER:
                return $lead->offer['hash'];

            case self::TARGET:
                return $lead->target['hash'];

            case self::TARGET_GEO:
                return $lead['country_id'];

            default:
                throw new \LogicException('Unknown group field for report.');

        }
    }

    private function getWith(): array
    {
        switch ($this->group_field) {
            case self::DATE:
                return ['advertiser_currency'];

            case self::PUBLISHER:
                return ['publisher', 'advertiser_currency'];

            case self::OFFER:
                return ['offer', 'advertiser_currency'];

            case self::TARGET:
                return ['target.template', 'advertiser_currency'];

            case self::TARGET_GEO:
                return ['country', 'advertiser_currency'];

            default:
                throw new \LogicException('Unknown group field for report.');

        }
    }

    private function getEmptyReportFields(): array
    {
        return [
            'total_count' => 0,
            'approved_count' => 0,
            'held_count' => 0,
            'cancelled_count' => 0,
            'trashed_count' => 0,
        ];
    }

    private function addAdditionalDataToField(&$field, Lead $lead)
    {
        /**
         * @var Payload $payload
         */
        $payload = $this->request->input('request_user')['payload'];
        switch ($this->group_field) {
            case self::PUBLISHER:
                if ($payload->get('foreign_user_hash')) {
                    $field['publisher_email'] = $lead->publisher['email'];
                }
                break;

            case self::OFFER:
                $field['offer_title'] = $lead->offer['title'];
                break;

            case self::TARGET:
                $field['target_title'] = trim($lead->target->template['title'] . ' ' . $lead->target['label']);
                break;

            case self::TARGET_GEO:
                $field['country_title'] = $lead->country['title'];
                $field['country_code'] = $lead->country['code'];
                $field['country_id'] = $lead->country['id'];
                break;
        }
    }
}
