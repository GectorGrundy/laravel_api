<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\Carbon;
use DB;
use Auth;
use App\Strategies\Statistic\PublisherDeviceReport;
use Dingo\Api\Routing\Helpers;
use App\Strategies\Statistic\{
    AdminLeadsStrategy, AdvertiserReportStrategy, AdvertiserLeadsStrategy, PublisherLeadsStrategy, PublisherReportStrategy
};
use App\Http\Requests\Statistic as R;
use App\Models\{
    HourlyStat, Lead, Order
};
use Illuminate\Support\Collection;

class StatisticController extends Controller
{
    use Helpers;

    private $_UNCHANGEABLE_ORDER_NAMES = [
        'test test', 'No name', 'Не указано', 'No definido', 'nicht angegeben', 'nu este definit'
    ];

    /**
     * Получение статистики по дням
     *
     * @param R\GetByDayRequest $request
     * @return array
     */
    public function getByDay(R\GetByDayRequest $request)
    {
        if (Auth::user()->isAdvertiser()) {
            $stat = (new AdvertiserReportStrategy())->get($request, AdvertiserReportStrategy::DATE);

        } else {
            $sum_fields = [
                'hits', 'flow_hosts', 'bot_count', 'safepage_count', 'traffback_count', 'approved_count',
                'held_count', 'cancelled_count', 'trashed_count', 'leads_payout', 'onhold_payout', 'oncancel_payout',
                'ontrash_payout',
            ];

            if (Auth::user()->isAdmin()) {
                $sum_fields[] = 'profit';
            }
            [$app_tz, $user_tz] = (new HourlyStat)->getTzs();

            $stat = HourlyStat::with($request->get('with', []))
                ->selectFields([DB::raw("DATE(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}')) as date")])
                ->sumFields($sum_fields)
                ->datetimeBetweenDates($request->get('date_from'), $request->get('date_to'))
                ->whereUser(Auth::id(), Auth::user()['role'], $request->get('publisher_hashes', []))
                ->whereFlow($request->get('flow_hashes', []))
                ->whereOffer($request->get('offer_hashes', []))
                ->whereLanding($request->get('landing_hashes', []))
                ->whereTransit($request->get('transit_hashes', []))
                ->whereCountries($request->get('country_ids', []))
                ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
                ->whereData1($request->get('data1', []))
                ->whereData2($request->get('data2', []))
                ->whereData3($request->get('data3', []))
                ->whereData4($request->get('data4', []))
                ->where('currency_id', $request->get('currency_id'))
                ->groupBy(DB::raw("DATE(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}'))"))
                ->get();

            $stat = $this->addEmptyFieldByDay(
                $stat,
                $sum_fields,
                $request->get('date_from', ''),
                $request->get('date_to', '')
            );
        }

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * Получение статистики по часам
     *
     * @param R\GetByDayHourRequest $request
     * @return array
     */
    public function getByDayHour(R\GetByDayHourRequest $request)
    {
        $sum_fields = [
            'hits', 'flow_hosts', 'bot_count', 'safepage_count', 'traffback_count',
            'approved_count', 'held_count', 'cancelled_count', 'trashed_count', 'leads_payout',
            'onhold_payout', 'oncancel_payout', 'ontrash_payout', 'bot_count',
        ];

        if (Auth::user()->isAdmin()) {
            $sum_fields[] = 'profit';
        }

        [$app_tz, $user_tz] = (new HourlyStat)->getTzs();

        $stat = HourlyStat::with($request->get('with', []))
            ->selectFields([
                DB::raw("DATE(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}')) as date"),
                DB::raw("HOUR(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}')) as hour")
            ])
            ->sumFields($sum_fields)
            ->datetimeBetweenDates($request->get('date_from'), $request->get('date_to'))
            ->whereUser(Auth::id(), Auth::user()['role'], $request->get('publisher_hashes', []))
            ->whereFlow($request->get('flow_hashes', []))
            ->whereOffer($request->get('offer_hashes', []))
            ->whereLanding($request->get('landing_hashes', []))
            ->whereTransit($request->get('transit_hashes', []))
            ->whereCountries($request->get('country_ids', []))
            ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
            ->whereData1($request->get('data1', []))
            ->whereData2($request->get('data2', []))
            ->whereData3($request->get('data3', []))
            ->whereData4($request->get('data4', []))
            ->where('currency_id', $request->get('currency_id'))
            ->groupBy([
                DB::raw("HOUR(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}'))"),
                DB::raw("DATE(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}'))")])
            ->get();

        $stat = $this->addEmptyFieldByDayHour($stat, $sum_fields, $user_tz);

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * Добавление пустых данных в результирующий массив статистики по часам дня
     *
     * @param Collection $stat
     * @param array $sum_fields
     * @param string $user_tz
     * @return mixed
     */
    private function addEmptyFieldByDayHour(Collection $stat, array $sum_fields, string $user_tz)
    {
        $stat = $stat->toArray();

        $example_stat = [];
        foreach ($sum_fields AS $field) {
            $example_stat[$field] = 0;
        }

        $example_stat['date'] = current($stat)['date'];

        $exists_hours = [];
        foreach ($stat AS $item) {
            $exists_hours[] = $item['hour'];
        }

        // Если это вчерашний день - заполняем нулями до самого последнего часа
        // Если сегодняшний - то текущего часа
        $current_date = Carbon::now($user_tz)->format('Y-m-d');
        $current_hour = Carbon::now($user_tz)->format('H');

        $last_needle_hour = (current($stat)['date'] === $current_date ? $current_hour : 23);

        for ($i = 0; $i <= $last_needle_hour; $i++) {
            $hour = $i . '';
            if ($i < 10) {
                $hour = '0' . $hour;
            }

            if (!in_array($hour, $exists_hours)) {
                $example_stat['hour'] = $hour;
                $stat[] = $example_stat;
            }
        }

        return collect($stat)->sortByDesc('hour')->values();
    }

    /**
     * Получение средних значений статистики по часам
     *
     * @param R\GetByHourRequest $request
     * @return array
     */
    public function getByHour(R\GetByHourRequest $request)
    {
        $sum_fields = [
            'hits', 'flow_hosts', 'bot_count', 'safepage_count', 'traffback_count', 'approved_count',
            'held_count', 'cancelled_count', 'trashed_count', 'leads_payout', 'onhold_payout', 'oncancel_payout',
            'ontrash_payout',
        ];

        if (Auth::user()->isAdmin()) {
            $sum_fields[] = 'profit';
        }

        [$app_tz, $user_tz] = (new HourlyStat)->getTzs();

        $stat = HourlyStat::with($request->get('with', []))
            ->selectFields([DB::raw("HOUR(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}')) as hour")])
            ->sumFields($sum_fields)
            ->datetimeBetweenDates($request->get('date_from'), $request->get('date_to'))
            ->whereUser(Auth::id(), Auth::user()['role'], $request->get('publisher_hashes', []))
            ->whereFlow($request->get('flow_hashes', []))
            ->whereOffer($request->get('offer_hashes', []))
            ->whereLanding($request->get('landing_hashes', []))
            ->whereTransit($request->get('transit_hashes', []))
            ->whereCountries($request->get('country_ids', []))
            ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
            ->whereData1($request->get('data1', []))
            ->whereData2($request->get('data2', []))
            ->whereData3($request->get('data3', []))
            ->whereData4($request->get('data4', []))
            ->where('currency_id', $request->get('currency_id'))
            ->groupBy(DB::raw("HOUR(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}'))"))
            ->get();

        $stat = $this->addEmptyFieldByHour($stat, array_merge($sum_fields, ['landing_unique_count']));

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * Добавление пустых данных в результирующий массив статистики по часам
     *
     * @param $stat
     * @param $average_fields
     * @return mixed
     */
    private function addEmptyFieldByHour($stat, $average_fields)
    {
        $stat = $stat->toArray();

        $example_stat = [];
        foreach ($average_fields AS $field) {
            $example_stat[$field] = 0;
        }

        // Получаем часы, по которым есть статистика
        $exists_hours = [];
        foreach ($stat AS $item) {
            $exists_hours[] = $item['hour'];
        }

        for ($i = 0; $i <= 23; $i++) {
            $hour = $i . '';
            if ($i < 10) {
                $hour = '0' . $hour;
            }

            if (!in_array($hour, $exists_hours)) {
                $example_stat['hour'] = $hour;
                $stat[] = $example_stat;
            }
        }

        return collect($stat)->sortByDesc('hour')->values();
    }

    /**
     * Получение статистики по потокам
     *
     * @param R\GetByFlowRequest $request
     * @return array
     */
    public function getByFlow(R\GetByFlowRequest $request)
    {
        $sum_fields = [
            'hits', 'flow_hosts', 'bot_count', 'safepage_count', 'traffback_count', 'approved_count',
            'held_count', 'cancelled_count', 'trashed_count', 'leads_payout', 'onhold_payout',
            'oncancel_payout', 'ontrash_payout', 'offer_hosts'
        ];

        if (Auth::user()->isAdmin()) {
            $sum_fields[] = 'profit';
        }

        $stat = HourlyStat::with($request->get('with', []))
            ->selectFields(['flows.title as flow_title', 'flows.hash as flow_hash'])
            ->sumFields($sum_fields)
            ->leftJoin('flows', 'flows.id', '=', 'hourly_stat.flow_id')
            ->datetimeBetweenDates($request->get('date_from'), $request->get('date_to'))
            ->whereUser(Auth::id(), Auth::user()['role'], $request->get('publisher_hashes', []))
            ->whereFlow($request->get('flow_hashes', []))
            ->whereOffer($request->get('offer_hashes', []))
            ->whereLanding($request->get('landing_hashes', []))
            ->whereTransit($request->get('transit_hashes', []))
            ->whereCountries($request->get('country_ids', []))
            ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
            ->whereData1($request->get('data1', []))
            ->whereData2($request->get('data2', []))
            ->whereData3($request->get('data3', []))
            ->whereData4($request->get('data4', []))
            ->where('currency_id', $request->get('currency_id'))
            ->groupBy('flow_id')
            ->get();

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * Получение статистики по паблишерам
     *
     * @param R\GetByPublisherRequest $request
     * @return array
     */
    public function getByPublisher(R\GetByPublisherRequest $request)
    {
        if (Auth::user()->isAdvertiser()) {
            $stat = (new AdvertiserReportStrategy())->get($request, AdvertiserReportStrategy::PUBLISHER);

        } else {
            $sum_fields = [
                'hits', 'transit_hosts', 'transit_landing_hosts', 'direct_landing_hosts', 'flow_hosts',
                'publisher_hosts', 'offer_hosts', 'system_hosts', 'traffback_count', 'traffic_cost', 'approved_count',
                'held_count', 'cancelled_count', 'trashed_count', 'leads_payout', 'onhold_payout',
                'oncancel_payout', 'ontrash_payout', 'bot_count', 'comeback_landing_hosts', 'noback_landing_hosts',
                'safepage_count', 'profit'
            ];

            $stat = HourlyStat::with($request->get('with', []))
                ->selectFields(['users.email as user_email', 'users.hash as user_hash'])
                ->sumFields($sum_fields)
                ->leftJoin('users', 'users.id', '=', 'hourly_stat.publisher_id')
                ->datetimeBetweenDates($request->get('date_from'), $request->get('date_to'))
                ->whereUser(Auth::id(), Auth::user()['role'], $request->get('publisher_hashes', []))
                ->whereFlow($request->get('flow_hashes', []))
                ->whereOffer($request->get('offer_hashes', []))
                ->whereLanding($request->get('landing_hashes', []))
                ->whereTransit($request->get('transit_hashes', []))
                ->whereCountries($request->get('country_ids', []))
                ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
                ->whereData1($request->get('data1', []))
                ->whereData2($request->get('data2', []))
                ->whereData3($request->get('data3', []))
                ->whereData4($request->get('data4', []))
                ->where('currency_id', $request->get('currency_id'))
                ->groupBy('publisher_id')
                ->get();
        }

        return ['response' => $stat, 'status_code' => 200];
    }

    public function getByOffer(R\GetByOfferRequest $request)
    {
        if (Auth::user()->isAdvertiser()) {
            $stat = (new AdvertiserReportStrategy())->get($request, AdvertiserReportStrategy::OFFER);

        } else {
            $sum_fields = [
                'hits', 'flow_hosts', 'offer_hosts', 'bot_count', 'safepage_count', 'traffback_count', 'approved_count',
                'held_count', 'cancelled_count', 'trashed_count', 'leads_payout', 'onhold_payout', 'oncancel_payout',
                'ontrash_payout',
            ];

            if (Auth::user()->isAdmin()) {
                $sum_fields[] = 'profit';
            }

            $stat = HourlyStat::with($request->get('with', []))
                ->selectFields(['offers.title as offer_title', 'offers.hash as offer_hash'])
                ->sumFields($sum_fields)
                ->leftJoin('offers', 'offers.id', '=', 'hourly_stat.offer_id')
                ->datetimeBetweenDates($request->get('date_from'), $request->get('date_to'))
                ->whereUser(Auth::id(), Auth::user()['role'], $request->get('publisher_hashes', []))
                ->whereFlow($request->get('flow_hashes', []))
                ->whereOffer($request->get('offer_hashes', []))
                ->whereLanding($request->get('landing_hashes', []))
                ->whereTransit($request->get('transit_hashes', []))
                ->whereCountries($request->get('country_ids', []))
                ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
                ->whereData1($request->get('data1', []))
                ->whereData2($request->get('data2', []))
                ->whereData3($request->get('data3', []))
                ->whereData4($request->get('data4', []))
                ->where('hourly_stat.currency_id', $request->get('currency_id'))
                ->groupBy('hourly_stat.offer_id')
                ->get();
        }

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * @api {GET} /stat.getByGeo stat.getByGeo
     * @apiGroup HourlyStat
     * @apiPermission admin
     * @apiPermission publisher
     * @apiParam {Number} currency_id
     * @apiParam {String[]=country_id,target_geo_country_id} group_by
     * @apiParam {String} [date_from=today]
     * @apiParam {String} [date_to=today]
     * @apiParam {String[]} [flow_hashes]
     * @apiParam {String[]} [offer_hashes]
     * @apiParam {String[]} [landing_hashes]
     * @apiParam {String[]} [transit_hashes]
     * @apiParam {Number[]} [country_ids]
     * @apiParam {Number[]} [publisher_hashes] Only for admin user role
     * @apiParam {String[]=country,target_geo_country} [with[]]
     * @apiSampleRequest /stat.getByGeo
     */
    public function getByGeo(R\GetByGeoRequest $request)
    {
        $sum_fields = [
            'hits', 'flow_hosts', 'bot_count', 'safepage_count', 'traffback_count', 'approved_count',
            'held_count', 'cancelled_count', 'trashed_count', 'leads_payout', 'onhold_payout', 'oncancel_payout',
            'ontrash_payout',
        ];

        if (Auth::user()->isAdmin()) {
            $sum_fields[] = 'profit';
        }

        $group_by = $request->input('group_by');

        $stat = HourlyStat::with($request->get('with', []))
            ->selectFields([$group_by])
            ->sumFields($sum_fields)
            ->datetimeBetweenDates($request->get('date_from'), $request->get('date_to'))
            ->whereUser(Auth::user()['id'], Auth::user()['role'], $request->get('publisher_hashes', []))
            ->whereFlow($request->get('flow_hashes', []))
            ->whereOffer($request->get('offer_hashes', []))
            ->whereLanding($request->get('landing_hashes', []))
            ->whereTransit($request->get('transit_hashes', []))
            ->whereCountries($request->get('country_ids', []))
            ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
            ->whereData1($request->get('data1', []))
            ->whereData2($request->get('data2', []))
            ->whereData3($request->get('data3', []))
            ->whereData4($request->get('data4', []))
            ->where('currency_id', $request->get('currency_id'))
            ->groupBy($group_by)
            ->get();

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * Получение статистики по регионам
     *
     * @param R\GetByRegionRequest $request
     * @return array
     */
    public function getByRegion(R\GetByRegionRequest $request)
    {
        $sum_fields = [
            'hits', 'flow_hosts', 'bot_count', 'safepage_count', 'traffback_count', 'approved_count',
            'held_count', 'cancelled_count', 'trashed_count', 'leads_payout', 'onhold_payout', 'oncancel_payout',
            'ontrash_payout',
        ];

        if (Auth::user()->isAdmin()) {
            $sum_fields[] = 'profit';
        }

        $stat = HourlyStat::with($request->get('with', []))
            ->selectFields(['hourly_stat.region_id', 'hourly_stat.country_id as country_id'])
            ->sumFields($sum_fields)
            ->datetimeBetweenDates($request->get('date_from'), $request->get('date_to'))
            ->whereUser(Auth::id(), Auth::user()['role'], $request->get('publisher_hashes', []))
            ->whereFlow($request->get('flow_hashes', []))
            ->whereOffer($request->get('offer_hashes', []))
            ->whereLanding($request->get('landing_hashes', []))
            ->whereTransit($request->get('transit_hashes', []))
            ->whereCountries($request->get('country_ids', []))
            ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
            ->whereData1($request->get('data1', []))
            ->whereData2($request->get('data2', []))
            ->whereData3($request->get('data3', []))
            ->whereData4($request->get('data4', []))
            ->where('currency_id', $request->get('currency_id'))
            ->groupBy('region_id')
            ->get();

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * Получение статистики по регионам
     *
     * @param R\GetByCityRequest $request
     * @return array
     */
    public function getByCity(R\GetByCityRequest $request)
    {
        $sum_fields = [
            'hits', 'flow_hosts', 'bot_count', 'safepage_count', 'traffback_count', 'approved_count',
            'held_count', 'cancelled_count', 'trashed_count', 'leads_payout', 'onhold_payout', 'oncancel_payout',
            'ontrash_payout',
        ];

        if (Auth::user()->isAdmin()) {
            $sum_fields[] = 'profit';
        }

        $stat = HourlyStat::with($request->get('with', []))
            ->selectFields([
                'hourly_stat.city_id',
                'hourly_stat.region_id as region_id',
                'hourly_stat.country_id as country_id'
            ])
            ->sumFields($sum_fields)
            ->leftJoin('cities', 'cities.id', '=', 'hourly_stat.city_id')
            ->datetimeBetweenDates($request->get('date_from'), $request->get('date_to'))
            ->whereUser(Auth::id(), Auth::user()['role'], $request->get('publisher_hashes', []))
            ->whereFlow($request->get('flow_hashes', []))
            ->whereOffer($request->get('offer_hashes', []))
            ->whereRegion($request->get('region_ids', []))
            ->whereLanding($request->get('landing_hashes', []))
            ->whereTransit($request->get('transit_hashes', []))
            ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
            ->whereData1($request->get('data1', []))
            ->whereData2($request->get('data2', []))
            ->whereData3($request->get('data3', []))
            ->whereData4($request->get('data4', []))
            ->where('currency_id', $request->get('currency_id'))
            ->groupBy('city_id')
            ->get();

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * Получение статистики по лендингам
     *
     * @param R\GetByLandingRequest $request
     * @return array
     */
    public function getByLanding(R\GetByLandingRequest $request)
    {
        $sum_fields = [
            'hits', 'flow_hosts', 'bot_count', 'traffback_count', 'approved_count', 'held_count',
            'cancelled_count', 'trashed_count', 'leads_payout', 'onhold_payout', 'oncancel_payout', 'ontrash_payout',
            'transit_landing_hosts', 'direct_landing_hosts', 'comeback_landing_hosts', 'noback_landing_hosts',
        ];

        if (Auth::user()->isAdmin()) {
            $sum_fields[] = 'profit';
        }

        $stat = HourlyStat::with([
            'landing' => function ($query) {
                $query->withTrashed();
            },
            'landing.locale'
        ])
            ->selectFields(['landing_id'])
            ->sumFields($sum_fields)
            ->datetimeBetweenDates($request->get('date_from'), $request->get('date_to'))
            ->whereUser(Auth::id(), Auth::user()['role'], $request->get('publisher_hashes', []))
            ->whereFlow($request->get('flow_hashes', []))
            ->whereOffer($request->get('offer_hashes', []))
            ->whereLanding($request->get('landing_hashes', []))
            ->whereTransit($request->get('transit_hashes', []))
            ->whereCountries($request->get('country_ids', []))
            ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
            ->whereData1($request->get('data1', []))
            ->whereData2($request->get('data2', []))
            ->whereData3($request->get('data3', []))
            ->whereData4($request->get('data4', []))
            ->where('hourly_stat.currency_id', $request->get('currency_id'))
            ->where('hourly_stat.landing_id', '!=', 0)
            ->groupBy('hourly_stat.landing_id')
            ->get();

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * Получение статистики по прелендингам
     *
     * @param R\GetByTransitRequest $request
     * @return array
     */
    public function getByTransit(R\GetByTransitRequest $request)
    {
        $sum_fields = ['hits', 'transit_hosts', 'bot_count', 'flow_hosts', 'traffback_count', 'traffic_cost',
            'approved_count', 'held_count', 'cancelled_count', 'trashed_count', 'leads_payout', 'onhold_payout',
            'oncancel_payout', 'ontrash_payout', 'transit_landing_count',
        ];

        if (Auth::user()->isAdmin()) {
            $sum_fields[] = 'profit';
        }

        $stat = HourlyStat::with([
            'transit' => function ($query) {
                $query->withTrashed();
            },
            'transit.locale'
        ])
            ->selectFields(['transit_id'])
            ->sumFields($sum_fields)
            ->datetimeBetweenDates($request->get('date_from'), $request->get('date_to'))
            ->whereUser(Auth::id(), Auth::user()['role'], $request->get('publisher_hashes', []))
            ->whereFlow($request->get('flow_hashes', []))
            ->whereOffer($request->get('offer_hashes', []))
            ->whereLanding($request->get('landing_hashes', []))
            ->whereTransit($request->get('transit_hashes', []))
            ->whereCountries($request->get('country_ids', []))
            ->whereTargetGeoCountry($request->get('target_geo_country_ids', []))
            ->whereData1($request->get('data1', []))
            ->whereData2($request->get('data2', []))
            ->whereData3($request->get('data3', []))
            ->whereData4($request->get('data4', []))
            ->where('currency_id', $request->get('currency_id'))
            ->groupBy('transit_id')
            ->get();

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * @api {GET} /stat.getByLead stat.getByLead
     * @apiGroup HourlyStat
     * @apiPermission admin
     * @apiPermission publisher
     * @apiPermission advetiser
     *
     * @apiParam {Number=1,3,5} currency_id Filter by currency for publisher and admin roles
     * @apiParam {Number[]=1,3,5} [currency_ids[]] Filter by currency for advertiser role
     *
     * @apiParam {Number=01...23} [hour]
     * @apiParam {String[]} [flow_hashes[]]
     * @apiParam {String[]} [offer_hashes[]]
     * @apiParam {Number[]} [offer_ids[]]
     * @apiParam {String[]} [publisher_hashes[]]
     * @apiParam {Number[]} [publisher_ids[]]
     * @apiParam {Number[]} [advertiser_ids[]]
     * @apiParam {String[]} [landing_hashes[]]
     * @apiParam {String[]} [transit_hashes[]]
     * @apiParam {String[]=Desctop=>1, MobilePhone=>2,Tablet=>3} [device_type_ids[]]
     * @apiParam {String[]} [os_platform_ids[]]
     * @apiParam {String[]} [browser_ids[]]
     * @apiParam {String[]} [offer_hashes[]]
     * @apiParam {String[]=new,approved,cancelled,trashed} [lead_statuses]
     * @apiParam {Number[]} [target_geo_country_ids] Filter by target geo country
     * @apiParam {Number[]} [country_ids] Filter by visitor country
     * @apiParam {Number} [region_id]
     * @apiParam {Number} [city_id]
     *
     * @apiParam {Date} [date_from="7 days ago"]
     * @apiParam {Date} [date_to="today"]
     * @apiParam {String=created_at,processed_at} [group_by] Date filter column for advertiser
     * @apiParam {String=created_at,processed_at} [date_filter_column="created_at"] Date filter column for admin
     *
     * @apiParam {String=created_at,initialized_at,processed_at} [sort_by]
     * @apiParam {String=asc,desc} [sorting] Required if sort_by is set
     *
     * @apiParam {String} [search_field] Advertiser allowed values: <code>publisher_hash,flow_hash,phone,hash</code><br>
     * @apiParam {String} [search] Value of search_field column
     * Admin allowed values: <code>id,hash,phone,name</code>
     *
     * @apiSampleRequest /stat.getByLead
     */
    public function getByLead(R\GetByLeadRequest $request): array
    {
        switch (Auth::user()['role']) {
            case 'administrator':
                $leads = (new AdminLeadsStrategy())->get($request);
                break;

            case 'advertiser':
                $leads = (new AdvertiserLeadsStrategy())->get($request);
                break;

            case 'publisher':
                $leads = (new PublisherLeadsStrategy())->get($request);
                $leads['data'] = $this->hideOrderPersonalFields($leads['data']);
                break;
        }

        return ['response' => $leads, 'status_code' => 200];
    }

    /**
     * Обработка результирующего набора статистики по лидам(замена телефона и имя заказа на *)
     *
     * @param array $leads
     * @return array
     */
    private function hideOrderPersonalFields($leads)
    {
        // Скрывается имя и номер телефона
        foreach ($leads AS $key => &$lead) {

            if (Auth::user()->isAdvertiser() && $lead['status'] !== Lead::NEW) {
                continue;
            }

            $lead['order']['phone'] = hidePartOfString($lead['order']['phone'], 3, '*', 'end');

            // Если имя в заказе не по-умолчанию
            if (!in_array($lead['order']['name'], $this->_UNCHANGEABLE_ORDER_NAMES)) {

                $name_parts = explode(' ', $lead['order']['name']);

                $lead['order']['name'] = '';

                foreach ($name_parts AS $part) {
                    $lead['order']['name'] .= hidePartOfString($part, 3, '*') . ' ';
                }
                $lead['order']['name'] = trim($lead['order']['name']);
            }
        }

        return $leads;
    }

    /**
     * Добавление пустых данных в результирующий массив статистики по дням
     *
     * @param $stat
     * @param $sum_fields
     * @param $date_from
     * @param $date_to
     * @return mixed
     */
    private function addEmptyFieldByDay($stat, $sum_fields, $date_from, $date_to)
    {
        $stat = $stat->toArray();

        $example_stat = [];
        foreach ($sum_fields AS $field) {
            $example_stat[$field] = 0;
        }

        $exists_dates = [];
        foreach ($stat AS $item) {
            $exists_dates[] = $item['date'];
        }

        $from = new \DateTime($date_from);
        $to = new \DateTime($date_to);

        $period = new \DatePeriod($from, new \DateInterval('P1D'), $to);

        $array_needle_dates = array_map(
            function ($item) {
                return $item->format('Y-m-d');
            },
            iterator_to_array($period)
        );

        //Добавляем в массив нужных дат значение "Дата по"
        $array_needle_dates[] = $date_to;

        foreach ($array_needle_dates AS $date) {
            if (!in_array($date, $exists_dates)) {
                $example_stat['date'] = $date;
                $stat[] = $example_stat;
            }
        }

        return $stat;
    }

    /**
     * Get lead info by hash
     *
     * @param R\GetLeadInfoByHashRequest $request
     * @return array
     */
    public function getLeadInfoByHash(R\GetLeadInfoByHashRequest $request)
    {
        $lead_info = Lead::find($request->get('id'));

        return ['status_code' => 200, 'response' => $lead_info];
    }

    /**
     *  Get order info by hash
     *
     * @param R\GetOrderInfoRequest $request
     * @return array
     */
    public function getOrderInfo(R\GetOrderInfoRequest $request)
    {
        $order_info = Order::find($request->get('id'));
        if (null === $order_info) {
            $order_info = [];
        }

        return ['status_code' => 200, 'response' => $order_info];
    }

    public function getByTargets(R\GetByTargetsRequest $request)
    {
        $stat = (new AdvertiserReportStrategy())->get($request, AdvertiserReportStrategy::TARGET);

        return ['response' => $stat, 'status_code' => 200];

    }

    public function getByTargetGeo(R\GetByTargetGeoRequest $request)
    {
        $stat = (new AdvertiserReportStrategy())->get($request, AdvertiserReportStrategy::TARGET_GEO);

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * @api {GET} /stat.getReport stat.getReport
     * @apiGroup HourlyStat
     * @apiPermission publisher
     *
     * @apiParam {Number=1,2,3,4} level
     * @apiParam {Date} date_from
     * @apiParam {Date} date_to
     * @apiParam {Number=1,3,5} [currency_id]
     *
     * @apiParam {Number} country_ids[]
     * @apiParam {Number} target_geo_country_ids[]
     * @apiParam {Number} flow_hashes[]
     * @apiParam {String} group_field <code>datetime, hour, week_day, offer, flow, landing, transit, device_type, os_platform, browser, offer_country, country, target_geo_country, region, city, data1, data2, data3, data4</code><br>
     *
     * @apiParam {String=asc,desc} sorting
     *
     * @apiParam {String} sort_by
     * <code>
     * title,total_count,approve,real_approve,approved_count,held_count,cancelled_count,trashed_count,cr, cr_unique, epc, epc_unique
     * expected_approve,bot_count, safepage_count, flow_hosts, hits, traffback_count, held_payout
     * </code><br>
     *
     * @apiParam {String} [parent_field] Cannot be the same as `group_field`
     * Publisher allowed values: <code>datetime, hour, week_day, offer, flow, landing, transit, device_type, os_platform, browser, offer_country, country, target_geo_country, region, city, data1, data2, data3, data</code>
     *
     * @apiParam {String} [parent_value]
     *
     * @apiParam {String} [parent_parent_field] Cannot be the same as `group_field` and `parent_value`
     * Publisher allowed values: <code>datetime, hour, week_day, offer, flow, landing, transit, device_type, os_platform, browser, offer_country, country, target_geo_country, region, city, data1, data2, data3, data</code>
     *
     * @apiParam {String} [parent_parent_value]
     *
     * @apiSampleRequest /stat.getReport
     */
    public function getReport(R\GetReportRequest $request)
    {
        $stat = (new PublisherReportStrategy())->get($request);

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * @api {GET} /stat.getDeviceReport stat.getDeviceReport
     * @apiGroup DeviceStat
     * @apiPermission publisher
     *
     * @apiParam {Number=1,2,3,4} level
     * @apiParam {Date} date_from
     * @apiParam {Date} date_to
     * @apiParam {Number=1,3,5} [currency_id]
     *
     * @apiParam {Number} country_ids[]
     * @apiParam {Number} target_geo_country_ids[]
     * @apiParam {Number} flow_hashes[]
     * @apiParam {String} group_field <code>datetime, offer,target_geo_country, device_type, os_platform, browser, landing, transit, data1, data2, data3, data4</code><br>
     *
     * @apiParam {String=asc,desc} sorting
     *
     * @apiParam {String} sort_by
     * <code>
     * title, total_count, real_approve, approve, approved_count, held_count, cancelled_count, trashed_count, cr, cr_unique, epc, epc_unique
     * expected_approve, bot_count, safepage_count, flow_hosts, hits, traffback_count, held_payout
     * </code><br>
     *
     * @apiParam {String} [parent_field] Cannot be the same as `group_field`
     * Publisher allowed values: <code>datetime, offer target_geo_country, device_type, os_platform, browser, landing, transit, data1, data2, data3, data4</code><br>
     *
     * @apiParam {String} [parent_value]
     *
     * @apiParam {String} [parent_parent_field] Cannot be the same as `group_field` and `parent_value`
     * Publisher allowed values: <code>datetime, offer, target_geo_country, device_type, os_platform, browser, landing, transit, data1, data2, data3, data4</code><br>
     *
     * @apiParam {String} [parent_parent_value]
     *
     * @apiSampleRequest /stat.getDeviceReport
     */
    public function getDeviceReport(R\GetDeviceReportRequest $request)
    {
        $stat = (new PublisherDeviceReport())->get($request);

        return ['response' => $stat, 'status_code' => 200];
    }
}
