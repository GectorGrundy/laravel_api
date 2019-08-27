<?php
declare(strict_types=1);

namespace App\Strategies\Statistic;

use App\Models\AbstractEntity;
use App\Classes\Statistics;
use App\Models\DeviceStat;
use App\Models\HourlyStat;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublisherReportStrategy implements AbstractStatisticStrategy
{
    public const DATETIME = 'datetime';
    public const WEEK_DAY = 'week_day';
    public const HOUR = 'hour';

    public const OFFER = 'offer';
    public const FLOW = 'flow';
    public const LANDING = 'landing';
    public const TRANSIT = 'transit';

    public const OS_PLATFORM = 'os_platform';
    public const BROWSER = 'browser';
    public const DEVICE_TYPE = 'device_type';

    public const OFFER_COUNTRY = 'offer_country';
    public const TARGET_GEO_COUNTRY = 'target_geo_country';
    public const COUNTRY = 'country';
    public const REGION = 'region';
    public const CITY = 'city';

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

        $model = $this->getModel();

        $query = $model->with($this->getWith())
            ->datetimeBetweenDates($request->input('date_from'), $request->input('date_to'))
            ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
            ->whereCurrencies([$request->input('currency_id')])
            ->whereUser($user['id'], User::PUBLISHER)
            ->whereOffer($request->get('offer_hashes', []))
            ->search($request->get('search_field'), $request->get('search'));;

        $query = $this->addParentConstraints($query);
        $query = $this->addSelects($query);

        $publisher_stats = $query->get();

        $result = [
            'total' => $this->getEmptyReportFields(),
            'stats' => [],
        ];

        foreach ($publisher_stats as $publisher_stat) {
            $id = $this->getIdByGroupField($publisher_stat);
            if ($id) {
                if (!isset($result['stats'][$id])) {
                    $result['stats'][$id] = $this->getEmptyReportFields();
                    $result['stats'][$id]['id'] = $id;

                    $this->addAdditionalDataToField($result['stats'][$id], $publisher_stat, $id);
                }

                $result['stats'][$id] = $this->addResultFields($result['stats'][$id], $publisher_stat);
                $result['stats'][$id] = $this->addResultCoefficientsFields($result['stats'][$id]);
            }
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

    private function getModel(): AbstractEntity
    {
        $fields = [
            $this->request->input('group_field'),
            $this->request->input('level_1_field'),
            $this->request->input('level_2_field'),
            $this->request->input('level_3_field'),
            $this->request->input('level_4_field'),
        ];

        $device_stat_groupings = [
            PublisherReportStrategy::BROWSER,
            PublisherReportStrategy::DEVICE_TYPE,
            PublisherReportStrategy::OS_PLATFORM
        ];

        if (\count(array_intersect($device_stat_groupings, $fields))) {
            return new DeviceStat();
        }
        return new HourlyStat();
    }

    private function addSelects(Builder $query)
    {
        $current_group_field = [];

        switch ($this->group_field) {
            case self::OS_PLATFORM:
            case self::BROWSER:
            case self::DEVICE_TYPE:
            case self::OFFER:
            case self::FLOW:
            case self::LANDING:
            case self::TRANSIT:
            case self::TARGET_GEO_COUNTRY:
            case self::COUNTRY:
            case self::REGION:
            case self::CITY:
                $current_group_field = [$this->group_field . '_id'];
                break;

            case self::OFFER_COUNTRY:
                $current_group_field = ['offer_id', 'target_geo_country_id'];
                break;

            case self::DATETIME:
            case self::WEEK_DAY:
            case self::HOUR:
                $current_group_field = ['datetime'];
                break;

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

    private function addResultFields(array $result, $publisher_stat)
    {
        $result['total_count'] +=
            $publisher_stat['approved_count']
            + $publisher_stat['held_count']
            + $publisher_stat['cancelled_count']
            + $publisher_stat['trashed_count'];
        $result['total_payout'] +=
            $publisher_stat['leads_payout']
            + $publisher_stat['onhold_payout']
            + $publisher_stat['oncancel_payout']
            + $publisher_stat['ontrash_payout'];

        $result['approved_count'] += $publisher_stat['approved_count'];
        $result['leads_payout'] += $publisher_stat['leads_payout'];

        $result['held_count'] += $publisher_stat['held_count'];
        $result['onhold_payout'] += $publisher_stat['onhold_payout'];

        $result['cancelled_count'] += $publisher_stat['cancelled_count'];
        $result['oncancel_payout'] += $publisher_stat['oncancel_payout'];

        $result['trashed_count'] += $publisher_stat['trashed_count'];
        $result['ontrash_payout'] += $publisher_stat['ontrash_payout'];

        $result['hits'] += $publisher_stat['hits'];
        $result['flow_hosts'] += $publisher_stat['flow_hosts'];
        $result['safepage_count'] += $publisher_stat['safepage_count'];
        $result['bot_count'] += $publisher_stat['bot_count'];
        $result['traffback_count'] += $publisher_stat['traffback_count'];

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

    private function getIdByGroupField(AbstractEntity $publisher_stat)
    {
        switch ($this->group_field) {
            case self::DATETIME:
                return toDate($publisher_stat['datetime']);

            case self::HOUR:
                return (new Carbon($publisher_stat['datetime']))->format('H');

            case self::WEEK_DAY:
                return (new Carbon($publisher_stat['datetime']))->dayOfWeek;


            case self::OS_PLATFORM:
                return $this->getValueOrEmpty($publisher_stat->os_platform['id']);

            case self::BROWSER:
                return $this->getValueOrEmpty($publisher_stat->browser['id']);

            case self::DEVICE_TYPE:
                return $publisher_stat->device_type['id'];


            case self::OFFER:
                return $publisher_stat->offer['hash'];

            case self::FLOW:
                return $publisher_stat->flow['hash'];

            case self::LANDING:
                return $this->getValueOrEmpty($publisher_stat->landing['hash']);

            case self::TRANSIT:
                return $this->getValueOrEmpty($publisher_stat->transit['hash']);


            case self::OFFER_COUNTRY:
                return $publisher_stat->offer['hash'] . ',' . $publisher_stat->target_geo_country['id'];

            case self::TARGET_GEO_COUNTRY:
                return $publisher_stat->target_geo_country['id'];

            case self::COUNTRY:
                return $publisher_stat->country['id'];

            case self::REGION:
                return $publisher_stat->region['id'];

            case self::CITY:
                return $publisher_stat->city['id'];


            case self::DATA1:
                return $this->getValueOrEmpty($publisher_stat->data1);

            case self::DATA2:
                return $this->getValueOrEmpty($publisher_stat->data2);

            case self::DATA3:
                return $this->getValueOrEmpty($publisher_stat->data3);

            case self::DATA4:
                return $this->getValueOrEmpty($publisher_stat->data4);
        }
    }

    private function getValueOrEmpty($value)
    {
        return empty($value) ? 'empty' : $value;
    }

    private function getTitleByGroupField(AbstractEntity $publisher_stat): string
    {
        switch ($this->group_field) {
            case self::DATETIME:
                return Carbon::createFromFormat('Y-m-d H:i:s', $publisher_stat['datetime'])->format('d.m.Y');

            case self::HOUR:
                return (new Carbon($publisher_stat['datetime']))->format('H:i');

            case self::WEEK_DAY:
                return trans('week_days.'. (new Carbon($publisher_stat['datetime']))->dayOfWeek);


            case self::OS_PLATFORM:
                return $publisher_stat->os_platform['title'] ?? '';

            case self::BROWSER:
                return $publisher_stat->browser['title'] ?? '';

            case self::DEVICE_TYPE:
                return $publisher_stat->device_type['title'] ?? '';


            case self::OFFER:
                return $publisher_stat->offer['title'];

            case self::FLOW:
                return $publisher_stat->flow['title'];

            case self::LANDING:
                return $publisher_stat->landing['title'] ?? '';

            case self::TRANSIT:
                return $publisher_stat->transit['title'] ?? '';


            case self::OFFER_COUNTRY:
                return $publisher_stat->offer['title'] . ', ' . $publisher_stat->target_geo_country['title'];

            case self::TARGET_GEO_COUNTRY:
                return $publisher_stat->target_geo_country['title'];

            case self::COUNTRY:
                return $publisher_stat->country['title'];

            case self::REGION:
                return $publisher_stat->region['title'];

            case self::CITY:
                return $publisher_stat->city['title'];


            case self::DATA1:
                return $publisher_stat->data1;

            case self::DATA2:
                return $publisher_stat->data2;

            case self::DATA3:
                return $publisher_stat->data3;

            case self::DATA4:
                return $publisher_stat->data4;
        }
    }

    private function getWith(): array
    {
        switch ($this->group_field) {
            case self::OFFER:
                return ['offer'];

            case self::LANDING:
                return ['landing'];

            case self::TRANSIT:
                return ['transit'];

            case self::FLOW:
                return ['flow'];


            case self::OFFER_COUNTRY:
                return ['offer', 'target_geo_country'];

            case self::COUNTRY:
                return ['country'];

            case self::TARGET_GEO_COUNTRY:
                return ['target_geo_country'];

            case self::REGION:
                return ['region'];

            case self::CITY:
                return ['city'];


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

    private function addAdditionalDataToField(&$field, AbstractEntity $publisher_stat, $id)
    {
        $field['title'] = $this->getTitleByGroupField($publisher_stat);
        $field['group_field'] = $this->group_field;

        switch ($this->group_field) {
            case self::COUNTRY:
                $field['country_code'] = $publisher_stat->target_geo_country['code'];
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

            case self::WEEK_DAY:
                return $query->weekDay($value);

            case self::HOUR:
                return $query->datetimeHour($value);


            case self::BROWSER:
                return $query->where('browser_id', $value);

            case self::OS_PLATFORM:
                return $query->where('os_platform_id', $value);

            case self::DEVICE_TYPE:
                return $query->where('device_type_id', $value);


            case self::OFFER:
                return $query->where('offer_id', getIdFromHash($value));

            case self::FLOW:
                return $query->where('flow_id', getIdFromHash($value));

            case self::LANDING:
                return $query->where('landing_id', getIdFromHash($value));

            case self::TRANSIT:
                return $query->where('transit_id', getIdFromHash($value));


            case self::OFFER_COUNTRY:
                [$offer_hash, $coutry_id] = explode(',', $value);
                return $query
                    ->where('offer_id', getIdFromHash($offer_hash))
                    ->where('target_geo_country_id', $coutry_id);

            case self::TARGET_GEO_COUNTRY:
                return $query->where('target_geo_country_id', $value);

            case self::COUNTRY:
                return $query->where('country_id', $value);

            case self::REGION:
                return $query->where('region_id', $value);

            case self::CITY:
                return $query->where('city_id', $value);


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
            case self::LANDING:
            case self::TRANSIT:
            case self::DATA1:
            case self::DATA2:
            case self::DATA3:
            case self::DATA4:
                return $value !== 'empty' ? $value : '';

            default:
                return $value;
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
        return $this->group_field === self::DATETIME;
    }
}
