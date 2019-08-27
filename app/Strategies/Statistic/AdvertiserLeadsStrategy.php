<?php
declare(strict_types=1);

namespace App\Strategies\Statistic;

use DB;
use Auth;
use App\Classes\Statistics;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Payload;

class AdvertiserLeadsStrategy implements AbstractStatisticStrategy
{
    /**
     * @var Request
     */
    private $request;

    public function get(Request $request)
    {
        $this->request = $request;

        $query = Lead::whereAdvertiser(Auth::user()->role, Auth::user()->id, $request->get('advertiser_hashes', []))
            ->whereOffer($request->get('offer_hashes'))
            ->whereCountries($request->get('target_geo_country_ids', []))
            ->whereHash($request->get('lead_hashes', []))
            ->whereAdvertiserCurrencies($request->get('currency_ids', []))
            ->search($request->get('search_field'), $request->get('search'));

        if ($request->group_by === 'processed_at') {
            $query->processedBetweenDates($request->input('date_from'), $request->input('date_to'));
        } else {
            $query->createdBetweenDates($request->input('date_from'), $request->input('date_to'));
        }

        $page = (int)$this->request->get('page', 1);
        $per_page = (int)$this->request->get('per_page', 20);

        $total_leads = $this->getTotalLeads($query);
        $leads = $this->getLeads($query, $page, $per_page);

        return [
            'data' => $leads,
            'total' => $page === 1 ? $this->getTotal($query) : [],
            'all_loaded' => allEntitiesLoaded($total_leads, $page, $per_page),
        ];
    }

    private function getTotal(Builder $query)
    {
        $total = clone $query;
        $total_result = $total->select(
            'advertiser_currency_id',
            DB::raw('COUNT(*) as total_count'),
            DB::raw('SUM(`advertiser_payout`) as total_payout'),
            DB::raw("SUM(CASE WHEN `status` = '" . Lead::NEW . "' THEN 1 ELSE 0 END) as held_count"),
            DB::raw("SUM(CASE WHEN `status` = '" . Lead::NEW . "' THEN `advertiser_payout` ELSE 0 END) as held_payout"),

            DB::raw("SUM(CASE WHEN `status` = '" . Lead::APPROVED . "' THEN 1 ELSE 0 END) as approved_count"),
            DB::raw("SUM(CASE WHEN `status` = '" . Lead::APPROVED . "' THEN `advertiser_payout` ELSE 0 END) as approved_payout"),

            DB::raw("SUM(CASE WHEN `status` = '" . Lead::CANCELLED . "' THEN 1 ELSE 0 END) as cancelled_count"),
            DB::raw("SUM(CASE WHEN `status` = '" . Lead::CANCELLED . "' THEN `advertiser_payout` ELSE 0 END) as cancelled_payout"),

            DB::raw("SUM(CASE WHEN `status` = '" . Lead::TRASHED . "' THEN 1 ELSE 0 END) as trashed_count"),
            DB::raw("SUM(CASE WHEN `status` = '" . Lead::TRASHED . "' THEN `advertiser_payout` ELSE 0 END) as trashed_payout")
        )
            ->groupBy(['advertiser_currency_id'])
            ->get();

        $total = [
            'currencies' => [],
        ];
        $total_held_count = 0;
        $total_approved_count = 0;
        $total_cancelled_count = 0;
        $total_trashed_count = 0;

        foreach ($total_result as $item) {
            $total_held_count += (int)($item['held_count'] ?? 0);
            $total_approved_count += (int)($item['approved_count'] ?? 0);
            $total_cancelled_count += (int)($item['cancelled_count'] ?? 0);
            $total_trashed_count += (int)($item['trashed_count'] ?? 0);

            $total['currencies'][] = [
                'currency_id' => $item['advertiser_currency_id'] ?? 0,
                'total_count' => $item['total_count'] ?? 0,
                'total_payout' => $item['total_payout'] ?? 0,
                'held_count' => $item['held_count'] ?? 0,
                'held_payout' => $item['held_payout'] ?? 0,
                'approved_count' => $item['approved_count'] ?? 0,
                'approved_payout' => $item['approved_payout'] ?? 0,
                'cancelled_payout' => $item['cancelled_count'] ?? 0,
                'cancelled_count' => $item['cancelled_payout'] ?? 0,
                'trashed_count' => $item['trashed_count'] ?? 0,
                'trashed_payout' => $item['trashed_payout'] ?? 0,
            ];
        }

        $total['real_approve'] = Statistics::calculateRealApprove(
            $total_approved_count,
            $total_cancelled_count,
            $total_held_count,
            true
        );
        $total['approve'] = Statistics::calculateApprove(
            $total_approved_count,
            $total_cancelled_count,
            $total_held_count,
            $total_trashed_count,
            true
        );

        return $total;
    }

    private function getTotalLeads(Builder $query)
    {
        $total_leads = clone $query;

        return (int)($total_leads->select(DB::raw('COUNT(*) as `total_leads`'))
                ->when($this->request->input('lead_statuses'), function (Builder $builder) {
                    $builder->whereIn('status', $this->request->input('lead_statuses'));
                })
                ->first()['total_leads'] ?? 0);
    }

    private function getLeads(Builder $query, int $page, int $per_page)
    {
        $leads = clone $query;

        $offset = paginationOffset($page, $per_page);

        return $leads->with($this->getWith($this->request))
            ->when($this->request->input('lead_statuses'), function (Builder $builder) {
                $builder->whereIn('status', $this->request->input('lead_statuses'));
            })
            ->orderBy($this->request->get('sort_by', 'id'), $this->request->get('sorting', 'asc'))
            ->offset($offset)
            ->limit($per_page)
            ->get();
    }

    public function getWith(Request $request): array
    {
        return [
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
                $q->select('id', 'name', 'phone', 'number_type_id', 'info');
            },
            'flow' => function ($q) {
                $q->select('id', 'hash');
            },
            'publisher' => function ($q) use ($request) {
                if ($this->isAdmin($request)) {
                    $q->select('id', 'hash', 'email');
                } else {
                    $q->select('id', 'hash');
                }
            },
            'advertiser' => function ($q) {
                $q->select('id', 'hash');
            },
            'integration' => function ($q) {
                $q->select('id', 'title');
            },
            'status_log',
        ];
    }

    private function isAdmin(Request $request)
    {
        /**
         * @var Payload $payload
         */
        $payload = $request->input('request_user')['payload'];

        return $payload->get('foreign_user_hash');
    }
}
