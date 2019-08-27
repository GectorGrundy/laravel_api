<?php
declare(strict_types=1);

namespace App\Strategies\Statistic;

use App\Models\AbstractEntity;
use App\Models\HourlyStat;
use Carbon\Carbon;
use App\Models\User;
use App\Classes\Statistics;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\DeviceStat;

class PublisherDeviceReport implements AbstractStatisticStrategy
{
    public const DATETIME = 'datetime';
    public const OFFER = 'offer';
    public const LANDING = 'landing';
    public const TRANSIT = 'transit';
    public const TARGET_GEO_COUNTRY = 'target_geo_country';
    public const OS_PLATFORM = 'os_platform';
    public const BROWSER = 'browser';
    public const DEVICE_TYPE = 'device_type';
    public const DATA1 = 'data1';
    public const DATA2 = 'data2';
    public const DATA3 = 'data3';
    public const DATA4 = 'data4';

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
        $query = DeviceStat::with($this->getWith())
            ->datetimeBetweenDates($request->input('date_from'), $request->input('date_to'))
            ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
            ->whereCurrencies([$request->input('currency_id')])
            ->whereUser($user['id'], User::PUBLISHER)
            ->whereOffer($request->get('offer_hashes', []))
            ->search($request->get('search_field'), $request->get('search'));

        $query = $this->addParentConstraints($query);
        $query = $this->addSelects($query);
        $device_stats = $query->get();

        $result = [
            'total' => $this->getEmptyReportFields(),
            'stats' => [],
        ];

        foreach ($device_stats as $device_stat) {
            $id = $this->getIdByGroupField($device_stat);

            if (!isset($result['stats'][$id])) {
                $result['stats'][$id] = $this->getEmptyReportFields();
                $result['stats'][$id]['id'] = $id;

                $this->addAdditionalDataToField($result['stats'][$id], $device_stat, $id);
            }

            $result['stats'][$id] = $this->addResultFields($result['stats'][$id], $device_stat);
            $result['stats'][$id] = $this->addResultCoefficientsFields($result['stats'][$id]);
        }

        $prev = null;
        foreach ($result['stats'] as &$group) {

            $result['total'] = $this->addResultFields($result['total'], $group);

            if ($this->isDateGroupField()) {
                $this->getPrevDayDifference($group, $prev);
            }
            $prev = $group;
        }
        $result['total'] = $this->addResultCoefficientsFields($result['total']);

        // Sort results
        $result['stats'] = $this->sortResults(collect($result['stats']));

        unset($lead, $stat);

        return $result;
    }

    private function addSelects(Builder $query)
    {
        $current_group_field = [];

        switch ($this->group_field) {
            case self::OS_PLATFORM:
            case self::BROWSER:
            case self::DEVICE_TYPE:
            case self::OFFER:
            case self::LANDING:
            case self::TRANSIT:
            case self::TARGET_GEO_COUNTRY:
            $current_group_field = [$this->group_field . '_id'];
                break;

            case self::DATETIME:
            case self::DATA1:
            case self::DATA2:
            case self::DATA3:
            case self::DATA4:
                $current_group_field = [$this->group_field];
                break;
        }

        return $query->select(array_merge($current_group_field, [ 'approved_count', 'leads_payout', 'held_count', 'onhold_payout',
            'cancelled_count', 'oncancel_payout', 'trashed_count', 'ontrash_payout', 'hits', 'flow_hosts',
            'safepage_count', 'bot_count', 'traffback_count']));
    }

    private function addResultFields(array $result, $device_stat)
    {
        $result['total_count'] +=
            $device_stat['approved_count']
            + $device_stat['held_count']
            + $device_stat['cancelled_count']
            + $device_stat['trashed_count'];
        $result['total_payout'] +=
            $device_stat['leads_payout']
            + $device_stat['onhold_payout']
            + $device_stat['oncancel_payout']
            + $device_stat['ontrash_payout'];

        $result['approved_count'] += $device_stat['approved_count'];
        $result['leads_payout'] += $device_stat['leads_payout'];

        $result['held_count'] += $device_stat['held_count'];
        $result['onhold_payout'] += $device_stat['onhold_payout'];

        $result['cancelled_count'] += $device_stat['cancelled_count'];
        $result['oncancel_payout'] += $device_stat['oncancel_payout'];

        $result['trashed_count'] += $device_stat['trashed_count'];
        $result['ontrash_payout'] += $device_stat['ontrash_payout'];

        $result['hits'] += $device_stat['hits'];
        $result['flow_hosts'] += $device_stat['flow_hosts'];
        $result['safepage_count'] += $device_stat['safepage_count'];
        $result['bot_count'] += $device_stat['bot_count'];
        $result['traffback_count'] += $device_stat['traffback_count'];

        return $result;
    }

    private function addResultCoefficientsFields($result)
    {
        $result['cr'] = Statistics::calculateCr(
            (int)$result['approved_count'],
            (int)$result['hits']
        );
        $result['cr_unique'] = Statistics::calculateCrUnique(
            (int)$result['approved_count'],
            (int)$result['flow_hosts']
        );

        $result['epc'] = Statistics::calculateEpc(
            (float)$result['leads_payout'],
            (int)$result['hits']
        );
        $result['epc_unique'] = Statistics::calculateEpcUnique(
            (float)$result['leads_payout'],
            (int)$result['flow_hosts']
        );

        $result['real_approve'] = Statistics::calculateRealApprove(
            $result['approved_count'],
            $result['cancelled_count'],
            $result['held_count'],
            true
        );
        $result['approve'] = Statistics::calculateApprove(
            $result['approved_count'],
            $result['cancelled_count'],
            $result['held_count'],
            $result['trashed_count'],
            true
        );
        $result['expected_approve'] = Statistics::calculateExpectedApprove(
            (int)$result['approved_count'],
            (int)$result['cancelled_count'],
            (int)$result['held_count'],
            true
        );

        return $result;
    }

    private function getIdByGroupField(AbstractEntity $device_stat)
    {
        switch ($this->group_field) {
            case self::DATETIME:
                return toDate($device_stat['datetime']);

            case self::OFFER:
                return $device_stat->offer['hash'];

            case self::TARGET_GEO_COUNTRY:
                return $device_stat->target_geo_country['id'];

            case self::DEVICE_TYPE:
                return $device_stat->device_type['id'];

            case self::LANDING:
                return $this->getValueOrEmpty($device_stat->landing['hash']);

            case self::OS_PLATFORM:
                return $this->getValueOrEmpty($device_stat->os_platform['id']);

            case self::BROWSER:
                return $this->getValueOrEmpty($device_stat->browser['id']);

            case self::TRANSIT:
                return $this->getValueOrEmpty($device_stat->transit['hash']);

            case self::DATA1:
                return $this->getValueOrEmpty($device_stat->data1);

            case self::DATA2:
                return $this->getValueOrEmpty($device_stat->data2);

            case self::DATA3:
                return $this->getValueOrEmpty($device_stat->data3);

            case self::DATA4:
                return $this->getValueOrEmpty($device_stat->data4);
        }
    }

    private function getValueOrEmpty($value)
    {
        return empty($value) ? 'empty' : $value;
    }

    private function getTitleByGroupField(AbstractEntity $device_stat): string
    {
        switch ($this->group_field) {
            case self::DATETIME:
                return Carbon::createFromFormat('Y-m-d H:i:s', $device_stat['datetime'])->format('d.m.Y');

            case self::OFFER:
                return $device_stat->offer['title'];

            case self::TARGET_GEO_COUNTRY:
                return $device_stat->target_geo_country['title'];

            case self::LANDING:
                return $device_stat->landing['title'] ?? '';

            case self::OS_PLATFORM:
                return $device_stat->os_platform['title'] ?? '';

            case self::BROWSER:
                return $device_stat->browser['title'] ?? '';

            case self::DEVICE_TYPE:
                return $device_stat->device_type['title'] ?? '';

            case self::TRANSIT:
                return $device_stat->transit['title'] ?? '';

            case self::DATA1:
                return $device_stat->data1;

            case self::DATA2:
                return $device_stat->data2;

            case self::DATA3:
                return $device_stat->data3;

            case self::DATA4:
                return $device_stat->data4;
        }
    }

    private function getWith(): array
    {
        switch ($this->group_field) {
            case self::TARGET_GEO_COUNTRY:
                return ['target_geo_country'];

            case self::OFFER:
                return ['offer'];

            case self::TRANSIT:
                return ['transit'];

            case self::DEVICE_TYPE:
                return ['device_type'];

            case self::OS_PLATFORM:
                return ['os_platform'];

            case self::BROWSER:
                return ['browser'];

            default:
                return [];
        }
    }

    private function getEmptyReportFields()
    {
        return [
            'total_count' => 0,
            'total_payout' => 0,
            'approved_count' => 0,
            'leads_payout' => 0,
            'held_count' => 0,
            'cancelled_count' => 0,
            'oncancel_payout' => 0,
            'onhold_payout' => 0,
            'trashed_count' => 0,
            'ontrash_payout' => 0,
            'hits' => 0,
            'flow_hosts' => 0,
            'safepage_count' => 0,
            'bot_count' => 0,
            'traffback_count' => 0,
            'expected_approve' => 0,
            'real_approve' => 0,
            'approve' => 0,
            'cr' => 0,
            'cr_unique' => 0,
            'epc' => 0,
            'epc_unique' => 0,
        ];
    }

    private function addAdditionalDataToField(&$field, AbstractEntity $device_stat, $id)
    {
        $field['title'] = $this->getTitleByGroupField($device_stat);
        $field['group_field'] = $this->group_field;

        switch ($this->group_field) {
            case self::TARGET_GEO_COUNTRY:
                $field['country_code'] = $device_stat->target_geo_country['code'];
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

    /**
     * Добавляет условия запроса в зависимости от уровня иерархии отчета
     *
     * @param Builder $query
     * @return Builder
     */
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

    /**
     * Добавляет условия запроса в зависимости от уровня иерархии отчета
     *
     * @param Builder $query
     * @return Builder
     */
    private function addParentConstraint(Builder $query, string $field, $value)
    {
        $value = $this->getValueOrConvertEmpty($field, $value);

        switch ($field) {
            case self::DATETIME:
                return $query->datetimeDate($value);

            case self::OFFER:
                return $query->where('offer_id', getIdFromHash($value));

            case self::LANDING:
                return $query->where('landing_id', getIdFromHash($value));

            case self::TRANSIT:
                return $query->where('transit_id', getIdFromHash($value));

            case self::TARGET_GEO_COUNTRY:
                return $query->where('target_geo_country_id', $value);

            case self::BROWSER:
                return $query->where('browser_id', $value);

            case self::OS_PLATFORM:
                return $query->where('os_platform_id', $value);

            case self::DEVICE_TYPE:
                return $query->where('device_type_id', $value);

            case self::DATA1:
                return $query->where('data1', $value);

            case self::DATA2:
                return $query->where('data2', $value);

            case self::DATA3:
                return $query->where('data3', $value);

            case self::DATA4:
                return $query->where('data4', $value);
        }
    }

    private function getValueOrConvertEmpty($field, $value)
    {
        switch ($field) {
            case self::DATETIME:
            case self::OFFER:
            case self::TARGET_GEO_COUNTRY:
            case self::DEVICE_TYPE:
                return $value;

            case self::LANDING:
            case self::TRANSIT:
            case self::BROWSER:
            case self::OS_PLATFORM:
                return $value !== 'empty' ? $value : '';

            case self::DATA1:
            case self::DATA2:
            case self::DATA3:
            case self::DATA4:
                return $value !== 'empty' ? $value : '';
        }
    }

    private function sortResults(Collection $stats)
    {
        $sort_field = $this->getSortField();

        $descending = $this->request->input('sorting', 'asc') === 'desc';

        return $result['stats'] = $stats->sortBy($sort_field, SORT_REGULAR, $descending)->values()->toArray();
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
        return $this->group_field === self::DATETIME;
    }
}
