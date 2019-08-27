<?php
declare(strict_types=1);

namespace App\Strategies\Statistic;

use App\Classes\Statistics;
use App\Models\Lead;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublisherLeadsStrategy implements AbstractStatisticStrategy
{
    /**
     * @var Request
     */
    private $request;

    public function get(Request $request)
    {
        $this->request = $request;

        $query = Lead::createdBetweenDates($request->get('date_from'), $request->get('date_to'))
            ->wherePublisher(Auth::user()->id, Auth::user()->role, $request->get('publisher_hashes', []))
            ->whereHour($request->get('hour'))
            ->whereFlow($request->get('flow_hashes', []))
            ->whereOffer($request->get('offer_hashes', []))
            ->whereLanding($this->getValueOrConvertEmpty($request->get('landing_hashes', [])))
            ->whereTransits($this->getValueOrConvertEmpty($request->get('transit_hashes', [])))
            ->whereIpCountries($request->get('country_ids', []))
            ->whereCountries($request->get('target_geo_country_ids', []))
            ->whereRegion($request->get('region_id'))
            ->whereCity($request->get('city_id'))
            ->whereData1($this->getValueOrConvertEmpty($request->get('data1', []), true))
            ->whereData2($this->getValueOrConvertEmpty($request->get('data2', []), true))
            ->whereData3($this->getValueOrConvertEmpty($request->get('data3', []), true))
            ->whereData4($this->getValueOrConvertEmpty($request->get('data4', []), true))
            ->whereHash($request->get('lead_hashes', ''))
            ->whereDeviceType($this->getValueOrConvertEmpty($request->get('device_type_ids', [])))
            ->whereOsPlatform($this->getValueOrConvertEmpty($request->get('os_platform_ids'), []))
            ->whereBrowser($this->getValueOrConvertEmpty($request->get('browser_ids', [])))
            ->where('currency_id', $request->get('currency_id'));

        $page = (int)$request->get('page', 1);
        $per_page = (int)$request->get('per_page', 20);

        $total_leads = $this->getTotalLeads($query);
        $leads = $this->getLeads($query, $page, $per_page);

        return [
            'data' => $leads,
            'total' => $page === 1 ? $this->getTotal($query) : [],
            'all_loaded' => allEntitiesLoaded($total_leads, $page, $per_page),
        ];
    }

    private function getValueOrConvertEmpty($value, $is_data_param = false)
    {
        if (isset($value[0])) {
            if ($is_data_param) {
                return $value[0] !== 'empty' ? $value : [''];
            }
            return $value[0] !== 'empty' ? $value : [0];
        }
        return $value;
    }

    private function getTotal(Builder $query)
    {
        $total = clone $query;
        $total_result = $total->select(
            DB::raw("SUM(CASE WHEN `status` = 'new' THEN 1 ELSE 0 END) as held_count"),
            DB::raw("SUM(CASE WHEN `status` = 'new' THEN `payout` ELSE 0 END) as held_payout"),

            DB::raw("SUM(CASE WHEN `status` = 'approved' THEN 1 ELSE 0 END) as approved_count"),
            DB::raw("SUM(CASE WHEN `status` = 'approved' THEN `payout` ELSE 0 END) as approved_payout"),

            DB::raw("SUM(CASE WHEN `status` = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count"),
            DB::raw("SUM(CASE WHEN `status` = 'cancelled' THEN `payout` ELSE 0 END) as cancelled_payout"),

            DB::raw("SUM(CASE WHEN `status` = 'trashed' THEN 1 ELSE 0 END) as trashed_count"),
            DB::raw("SUM(CASE WHEN `status` = 'trashed' THEN `payout` ELSE 0 END) as trashed_payout"),

            DB::raw('COUNT(*) as total_count'),
            DB::raw('SUM(`payout`) as total_payout')
        )
            ->first();

        $total = [
            'total_count' => (int)($total_result['total_count'] ?? 0),
            'total_payout' => (float)($total_result['total_payout'] ?? 0),

            'held_count' => (int)($total_result['held_count'] ?? 0),
            'held_payout' => (float)($total_result['held_payout'] ?? 0),

            'approved_count' => (int)($total_result['approved_count'] ?? 0),
            'approved_payout' => (float)($total_result['approved_payout'] ?? 0),

            'cancelled_count' => (int)($total_result['cancelled_count'] ?? 0),
            'cancelled_payout' => (float)($total_result['cancelled_payout'] ?? 0),

            'trashed_count' => (int)($total_result['trashed_count'] ?? 0),
            'trashed_payout' => (float)($total_result['trashed_payout'] ?? 0),
        ];

        $held_count = (int)$total['held_count'];
        $approved_count = (int)$total['approved_count'];
        $cancelled_count = (int)$total['cancelled_count'];
        $trashed_count = (int)$total['trashed_count'];

        $total['real_approve'] = Statistics::calculateRealApprove(
            $approved_count,
            $cancelled_count,
            $held_count,
            true
        );
        $total['expected_approve'] = Statistics::calculateExpectedApprove(
            $approved_count,
            $cancelled_count,
            $held_count,
            true
        );
        $total['approve'] = Statistics::calculateApprove(
            $approved_count,
            $cancelled_count,
            $held_count,
            $trashed_count,
            true
        );

        return $total;
    }

    private function getStatusesFilter()
    {
        return $this->request->input('lead_statuses', [
            Lead::NEW,
            Lead::APPROVED,
            Lead::CANCELLED,
            Lead::TRASHED,
        ]);
    }

    private function getTotalLeads(Builder $query)
    {
        $total_leads = clone $query;
        return (int)($total_leads->select(DB::raw('COUNT(*) as `total_leads`'))->first()['total_leads'] ?? 0);
    }

    private function getWith()
    {
        return [
            'flow' => function ($q) {
                $q->select('id', 'title', 'hash');
            },
            'target' => function ($q) {
                $q->select('id', 'target_template_id', 'locale_id', 'label');
            },
            'target.template',
            'country' => function ($q) {
                $q->select('id', 'title', 'code')->translate();
            },
            'ip_country' => function ($q) {
                $q->select('id', 'title', 'code')->translate();
            },
            'city' => function ($q) {
                $q->select('id', 'country_id', 'title')->translate();
            },
            'offer' => function ($q) {
                $q->select('offers.id', 'title', 'hash');
            },
            'landing' => function ($q) {
                $q->select('id', 'title', 'locale_id');
            },
            'landing.locale' => function ($q) {
                $q->select('id', 'title', 'code');
            },
            'transit' => function ($q) {
                $q->select('id', 'title', 'locale_id');
            },
            'transit.locale' => function ($q) {
                $q->select('id', 'title', 'code');
            },
            'order' => function ($q) {
                $q->select('id', 'name', 'phone', 'number_type_id');
            },
        ];
    }

    private function getLeads(Builder $query, int $page, int $per_page)
    {
        $query = clone $query;

        $offset = paginationOffset($page, $per_page);

        return $query->with($this->getWith())
            ->whereIn('status', $this->getStatusesFilter())
            ->orderBy($this->request->get('sort_by', 'id'), $this->request->get('sorting', 'asc'))
            ->offset($offset)
            ->limit($per_page)
            ->get();
    }
}