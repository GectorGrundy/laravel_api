<?php
declare(strict_types=1);

namespace App\Strategies\Statistic;

use App\Models\Currency;
use App\Classes\Statistics;
use App\Models\Lead;
use App\Models\User;
use Hashids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdminReportStrategy implements AbstractStatisticStrategy
{
    public const CREATED_AT = 'created_at';
    public const PROCESSED_AT = 'processed_at';
    public const PROCESSED_AT_HELD = 'processed_at_held';
    public const PUBLISHER_HASH = 'publisher_hash';
    public const ADVERTISER_HASH = 'advertiser_hash';
    public const OFFER_HASH = 'offer_hash';
    public const COUNTRY_ID = 'country_id';
    public const TARGET_GEO = 'target_geo';

    /**
     * @var Request
     */
    private $request;
    /**
     * @var string
     */
    private $group_field;

    public function get(Request $request)
    {
        $this->request = $request;
        $this->group_field = $request->input('group_field');

        $user = \Auth::user();

        $query = Lead::with($this->getWith())
            ->createdBetweenDates($request->input('date_from'), $request->input('date_to'))
            ->whereCountries($request->get('target_geo_country_ids', []))
            ->whereCurrencies($request->get('currency_ids', []))
            ->whereAdvertiser(User::ADMINISTRATOR, $user['id'], $request->get('advertiser_hashes', []))
            ->wherePublisher($user['id'], User::ADMINISTRATOR, $request->get('publisher_hashes', []))
            ->whereOffer($request->get('offer_hashes', []))
            ->search($request->get('search_field'), $request->get('search'));

        $query = $this->addParentConstraints($query);
        $leads = $query->get();

        $result = [
            'total' => $this->getEmptyReportFields(),
            'stats' => [],
        ];

        foreach ($leads as &$lead) {

            $id = $this->getIdByGroupField($lead);

            if (!isset($result['stats'][$id])) {
                $result['stats'][$id] = $this->getEmptyReportFields();
                $result['stats'][$id]['id'] = $id;

                $this->addAdditionalDataToField($result['stats'][$id], $lead, $id);
            }

            $result['stats'][$id]['total_count'] += 1;
            $result['total']['total_count'] += 1;
            $payout = (float)$lead['payout'];
            $profit = (float)$lead['profit'];

            switch ($lead['status']) {
                case Lead::APPROVED;
                    $result['stats'][$id]['approved_count'] += 1;
                    $result['total']['approved_count'] += 1;

                    switch ($lead['currency_id']) {
                        case Currency::RUB_ID:
                            $result['stats'][$id]['rub_approved_payout'] += $payout;
                            $result['stats'][$id]['rub_profit'] += $profit;
                            $result['total']['rub_approved_payout'] += $payout;
                            $result['total']['rub_profit'] += $profit;
                            break;

                        case Currency::USD_ID:
                            $result['stats'][$id]['usd_approved_payout'] += $payout;
                            $result['stats'][$id]['usd_profit'] += $profit;
                            $result['total']['usd_approved_payout'] += $payout;
                            $result['total']['usd_profit'] += $profit;
                            break;

                        case Currency::EUR_ID:
                            $result['stats'][$id]['eur_approved_payout'] += $payout;
                            $result['stats'][$id]['eur_profit'] += $profit;
                            $result['total']['eur_approved_payout'] += $payout;
                            $result['total']['eur_profit'] += $profit;
                            break;
                    }
                    break;

                case Lead::NEW;
                    $result['stats'][$id]['held_count'] += 1;
                    $result['total']['held_count'] += 1;

                    switch ($lead['currency_id']) {
                        case Currency::RUB_ID:
                            $result['stats'][$id]['rub_held_payout'] += $payout;
                            $result['total']['rub_held_payout'] += $payout;
                            break;

                        case Currency::USD_ID:
                            $result['stats'][$id]['usd_held_payout'] += $payout;
                            $result['total']['usd_held_payout'] += $payout;
                            break;

                        case Currency::EUR_ID:
                            $result['stats'][$id]['eur_held_payout'] += $payout;
                            $result['total']['eur_held_payout'] += $payout;
                            break;
                    }
                    break;

                case Lead::CANCELLED;
                    $result['stats'][$id]['cancelled_count'] += 1;
                    $result['total']['cancelled_count'] += 1;
                    break;

                case Lead::TRASHED;
                    $result['stats'][$id]['trashed_count'] += 1;
                    $result['total']['trashed_count'] += 1;
                    break;
            }
        }

        // Calc approve
        $prev = null;
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

            if ($this->isDateGroupField()) {
                $this->getPrevDayDifference($group, $prev);
            }
            $prev = $group;
        }

        $result['stats'] = $this->sortResults(collect($result['stats']));

        $result['total']['real_approve'] = Statistics::calculateRealApprove(
            $result['total']['approved_count'],
            $result['total']['cancelled_count'],
            $result['total']['held_count'],
            true
        );

        $result['total']['approve'] = Statistics::calculateApprove(
            $result['total']['approved_count'],
            $result['total']['cancelled_count'],
            $result['total']['held_count'],
            $result['total']['trashed_count'],
            true
        );

        unset($lead, $stat);

        return $result;
    }

    private function getIdByGroupField(Lead $lead)
    {
        switch ($this->group_field) {
            case self::CREATED_AT:
                return toDate($lead['created_at']);

            case self::PROCESSED_AT_HELD:
                return \is_null($lead['processed_at']) ?
                    toDate($lead['created_at']) :
                    toDate($lead['processed_at']);

            case self::PROCESSED_AT:
                return \is_null($lead->processed_at) ?
                    '0' :
                    toDate($lead['processed_at']);

            case self::PUBLISHER_HASH:
                return $lead->publisher['hash'];

            case self::ADVERTISER_HASH:
                return \is_null($lead->advertiser) ? 0 : $lead->advertiser['hash'];

            case self::OFFER_HASH:
                return $lead->offer['hash'];

            case self::COUNTRY_ID:
                return $lead->country_id;

            case self::TARGET_GEO:
                return $lead->target_geo_id;
        }
    }

    private function getTitleByGroupField(Lead $lead): string
    {
        switch ($this->group_field) {
            case self::CREATED_AT:
                return toHumanDate($lead['created_at']);

            case self::PROCESSED_AT_HELD:
                return \is_null($lead['processed_at']) ?
                    toHumanDate($lead['created_at']) :
                    toHumanDate($lead['processed_at']);

            case self::PROCESSED_AT:
                return \is_null($lead->processed_at) ?
                    trans('leads.unprocessed') :
                    toHumanDate($lead['processed_at']);

            case self::PUBLISHER_HASH:
                return $lead->publisher['email'];

            case self::ADVERTISER_HASH:
                return \is_null($lead->advertiser) ? trans('leads.undefined') : $lead->advertiser['email'];

            case self::OFFER_HASH:
                return $lead->offer['title'];

            case self::COUNTRY_ID:
                return $lead->country['title'];

            case self::TARGET_GEO:
                return $lead->offer['title'] . ' ' . $lead->target_geo->country['title'];
        }
    }

    private function getWith(): array
    {
        switch ($this->request->group_field) {
            case self::PUBLISHER_HASH:
                return ['publisher'];

            case self::ADVERTISER_HASH:
                return ['advertiser'];

            case self::OFFER_HASH:
                return ['offer'];

            case self::COUNTRY_ID:
                return ['country'];

            case self::TARGET_GEO:
                return ['offer', 'target_geo.country'];

            default:
                return [];
        }
    }

    private function getEmptyReportFields()
    {
        return [
            'total_count' => 0,
            'approved_count' => 0,
            'held_count' => 0,
            'cancelled_count' => 0,
            'trashed_count' => 0,

            'rub_approved_payout' => 0,
            'rub_held_payout' => 0,
            'rub_profit' => 0,

            'usd_approved_payout' => 0,
            'usd_held_payout' => 0,
            'usd_profit' => 0,

            'eur_approved_payout' => 0,
            'eur_held_payout' => 0,
            'eur_profit' => 0,
        ];
    }

    private function addAdditionalDataToField(&$field, Lead $lead, $id)
    {
        $field['title'] = $this->getTitleByGroupField($lead);
        $field['group_field'] = $this->group_field;

        switch ($this->group_field) {
            case self::COUNTRY_ID:
                $field['country_code'] = $lead->country['code'];
                break;
        }
    }

    private function getPrevDayDifference(&$field, ?array $prev)
    {
        if (empty($field['id']) || empty($prev)) {
            return $this->returnEmptyDifferenceFields($field);
        }

        // Total
        $today_total = $field['total_count'];
        $yesterday_total = $prev['total_count'];

        if ($today_total && $yesterday_total) {
            $total_difference = $today_total - $yesterday_total;
            $field['total_difference'] = $this->getSign($total_difference)
                . abs((int)(100 * $yesterday_total / $today_total) - 100) . '%';
        } else {
            $field['total_difference'] = '0%';
        }

        // Approved
        $today_approved = $field['approved_count'];
        $yesterday_approved = $prev['approved_count'];

        if ($today_approved && $yesterday_approved) {
            $approved_difference = $today_approved - $yesterday_approved;
            $field['approved_difference'] = $this->getSign($approved_difference)
                . abs((int)(100 * $yesterday_approved / $today_approved) - 100) . '%';
        } else {
            $field['approved_difference'] = '0%';
        }
    }

    private function returnEmptyDifferenceFields(&$field)
    {
        $field['total_difference'] = '0%';
        $field['approved_difference'] = '0%';
    }

    private function getSign($difference)
    {
        if ($difference === 0) {
            return '';
        }
        if ($difference > 0) {
            return '+';
        }

        return '-';
    }

    private function addParentConstraints(Builder $query): Builder
    {
        $level = (int)$this->request->input('level');

        if ($level >= 2) {
            $this->addParentConstraint($query,
                $this->request->input('level_1_field'),
                $this->request->input('level_1_value')
            );
        }

        if ($level >= 3) {
            $this->addParentConstraint($query,
                $this->request->input('level_2_field'),
                $this->request->input('level_2_value')
            );
        }

        if ($level >= 4) {
            $this->addParentConstraint($query,
                $this->request->input('level_3_field'),
                $this->request->input('level_3_value')
            );
        }

        return $query;
    }


    private function addParentConstraint(Builder $query, string $field, $value)
    {
        switch ($field) {
            case self::CREATED_AT:
                return $query->createdBetweenDates($value, $value);

            case self::PROCESSED_AT:
                if (empty($value)) {
                    return $query->whereNull('processed_at');
                }
                return $query->processedBetweenDates($value, $value);

            case self::PROCESSED_AT_HELD:
                return $query->createdBetweenDates($value, $value);

            case self::PUBLISHER_HASH:
                return $query->where('publisher_id', Hashids::decode($value)[0] ?? 0);

            case self::ADVERTISER_HASH:
                return $query->where('advertiser_id', Hashids::decode($value)[0] ?? 0);

            case self::OFFER_HASH:
                return $query->where('offer_id', Hashids::decode($value)[0] ?? 0);

            case self::COUNTRY_ID:
            case self::TARGET_GEO:
                return $query->where('country_id', $value);
        }
    }

    private function sortResults(Collection $stats)
    {
        $sort_field = $this->getSortField();

        $desc = $this->request->input('sorting', 'asc') === 'desc';

        return $result['stats'] = $stats->sortBy($sort_field, SORT_REGULAR, $desc)->values()->toArray();
    }

    private function getSortField()
    {
        $sort_field = $this->request->input('sort_by', 'id');

        if ($sort_field === 'title' && $this->isDateGroupField()) {
            return 'id';
        }

        return $sort_field;
    }

    private function isDateGroupField()
    {
        return
            $this->group_field === self::CREATED_AT
            || $this->group_field === self::PROCESSED_AT
            || $this->group_field === self::PROCESSED_AT_HELD;
    }
}