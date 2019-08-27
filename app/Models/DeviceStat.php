<?php
declare(strict_types=1);

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Builder;
use UAParser\Result\Device;

/**
 * @todo Add GlobalUserEnabled scope
 * @method $this whereAdvertiser($user_role, $user_id, ?array $advertiser_hashes = [])
 * @method $this whereUser($user_id, $user_role, ?array $publisher_hashes = [])
 * @method $this whereCountries(array $country_ids = [])
 * @method $this whereTargetGeoCountry(array $targetGeoCountry = [])
 * @method $this whereLanding(array $landing_hashes = [])
 * @method $this whereTransit(array $transit_hashes = [])
 * @method $this whereCurrencies(array $currency_ids = [])
 * @method $this whereOffer(array $offer_hashes = [])
 * @method $this search(?string $search_field, ?string $search)
 * @property User publisher
 * @property Target target
 * @property Advertiser advertiser
 */
class DeviceStat extends AbstractEntity
{
    protected $table = 'device_stat';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function target_geo_country()
    {
        return $this->belongsTo(Country::class, 'target_geo_country_id');
    }

    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function transit()
    {
        return $this->belongsTo(Transit::class);
    }

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

    public function browser()
    {
        return $this->belongsTo(Browser::class);

    }

    public function os_platform()
    {
        return $this->belongsTo(OsPlatform::class);

    }

    public function device_type()
    {
        return $this->belongsTo(DeviceType::class);
    }

    public function scopeWhereUser(Builder $builder, $user_id, $user_role, $publisher_hashes = [])
    {
        switch ($user_role) {
            case 'administrator':
                if (\count($publisher_hashes) > 0) {
                    $builder->whereIn('device_stat.publisher_id', getIdsByHashes($publisher_hashes));
                }

                break;

            case 'publisher':
                $builder->where('device_stat.publisher_id', $user_id);
                break;
        }

        return $builder;
    }

    public function scopeWhereFlow(Builder $builder, $flows_hashes)
    {
        if (\is_array($flows_hashes) && \count($flows_hashes) > 0) {
            $builder->whereIn('device_stat.flow_id', getIdsByHashes($flows_hashes));
        }
        return $builder;
    }

    public function scopeWhereOffer(Builder $builder, $offers_hashes)
    {
        if (\is_array($offers_hashes) && \count($offers_hashes) > 0) {
            $builder->whereIn('device_stat.offer_id', getIdsByHashes($offers_hashes));
        }
        return $builder;
    }

    public function scopeWhereCountries(Builder $builder, $countries_ids)
    {
        if (\is_array($countries_ids) && \count($countries_ids) > 0) {
            $builder->whereIn('device_stat.country_id', $countries_ids);
        }
        return $builder;
    }

    public function scopeWhereTargetGeoCountry(Builder $builder, $country_ids)
    {
        if (\is_array($country_ids) && \count($country_ids) > 0) {
            $builder->whereIn('device_stat.target_geo_country_id', $country_ids);
        }

        return $builder;
    }

    public function scopeWhereCurrencies(Builder $builder, array $currency_ids = [])
    {
        if (!\count($currency_ids)) {
            return $builder;
        }

        return $builder->whereIn('currency_id', $currency_ids);
    }

    public function scopeWhereLanding(Builder $query, $landing_hashes)
    {
        if (\is_array($landing_hashes) && \count($landing_hashes) > 0) {
            $query->whereIn('hourly_stat.landing_id', getIdsByHashes($landing_hashes));
        }
        return $query;
    }

    public function scopeWhereTransit(Builder $query, $transit_hashes)
    {
        if (!\is_array($transit_hashes) || !\count($transit_hashes)) {
            return $query;
        }

        return $query->whereIn('hourly_stat.transit_id',
            collect(array_map(function ($hash) {
                    if ($hash === '0') {
                        return 0;
                    }

                    return Hashids::decode($hash)[0] ?? null;
                }, $transit_hashes)
            )->reject(function ($value) {
                return \is_null($value);
            })
        );
    }

    public function scopeWhereData1(Builder $builder, $data1_list)
    {
        if (\is_array($data1_list) && \count($data1_list) > 0) {
            $builder->whereIn('device_stat.data1', $data1_list);
        }

        return $builder;
    }

    public function scopeWhereData2(Builder $builder, $data2_list)
    {
        if (\is_array($data2_list) && \count($data2_list) > 0) {
            $builder->whereIn('device_stat.data2', $data2_list);
        }

        return $builder;
    }

    public function scopeWhereData3(Builder $builder, $data3_list)
    {
        if (\is_array($data3_list) && \count($data3_list) > 0) {
            $builder->whereIn('device_stat.data3', $data3_list);
        }

        return $builder;
    }

    public function scopeWhereData4(Builder $builder, array $data4_list)
    {
        if (\is_array($data4_list) && \count($data4_list) > 0) {
            $builder->whereIn('device_stat.data4', $data4_list);
        }

        return $builder;
    }

    /**
     * Скоуп, которые добавляет поля, которые нужно добавить в результирующий набор
     *
     * @param $builder
     * @param $select_fields
     * @return mixed
     */
    public function scopeSelectFields(Builder $builder, $select_fields)
    {
        if (\is_array($select_fields) && \count($select_fields) > 0) {
            foreach ($select_fields AS $field) {
                $builder->addSelect($field);
            }
        }

        return $builder;
    }

    /**
     * Скоуп, который добавляет поля в результирующий набор, которые нужно просумировать
     *
     * @param $builder
     * @param $sum_fields
     * @return mixed
     */
    public function scopeSumFields(Builder $builder, array $sum_fields)
    {
        if (\count($sum_fields)) {
            foreach ($sum_fields AS $field) {
                $builder->addSelect(DB::raw("SUM({$field}) AS {$field}"));
            }
        }

        return $builder;
    }

    /**
     * Скоуп, который добавляет поля в результирующий набор, по которым нужно получить средние значения
     *
     * @param $builder
     * @param $average_fields
     * @return mixed
     */
    public function scopeAverageFields(Builder $builder, array $average_fields)
    {
        if (\count($average_fields) > 0) {
            foreach ($average_fields AS $field) {
                $builder->addSelect(DB::raw("ROUND(AVG({$field}), 2) AS {$field}"));
            }
        }

        return $builder;
    }

    public function scopePrepareForGroupBy(Builder $builder, $group_by)
    {
        switch ($group_by) {
            case 'date':
                return $this->prepareForGroupByDate($builder);

            case 'device_type_id':
                return $this->prepareForGroupByDeviceTypeId($builder);

            case 'browser_id':
                return $this->prepareForGroupByBrowserId($builder);

            case 'os_platform_id':
                return $this->prepareForGroupByOsPlatformId($builder);

            case 'country_id':
                return $this->prepareForGroupByCountryId($builder);

            case 'data1':
                return $this->prepareForGroupByData1($builder);

            case 'data2':
                return $this->prepareForGroupByData2($builder);

            case 'data3':
                return $this->prepareForGroupByData3($builder);

            case 'data4':
                return $this->prepareForGroupByData4($builder);
        }
    }

    public function scopeSearch(Builder $builder, ?string $search_field, ?string $search)
    {
        // @todo remove
        if (is_null($search)) {
            $search = '';
        }

        $search = trim(urldecode($search));

        if (empty($search_field) || empty($search)) {
            return $builder;
        }

        switch ($search_field) {
            case 'id':
                return $builder->whereIn('id', explode(',', $search));

            case 'publisher_hash':
                $publisher_id = Hashids::decode($search)[0] ?? -1;
                return $builder->where('publisher_id', $publisher_id);

            case 'flow_hash':
                $flow_id = Hashids::decode($search)[0] ?? -1;
                return $builder->where('flow_id', $flow_id);

            case 'hash':
                $ids = [];
                $hashes = explode(',', $search);
                foreach ($hashes as $hash) {
                    try {
                        $ids[] = $this->getIdFromHash($hash);
                    } catch (NotDecodedHashException $e) {
                        continue;
                    }
                }
                return $builder->whereIn('id', $ids);

            case 'phone':
                if (!starts_with($search, '+')) {
                    $search = "+{$search}";
                }
                return $builder->whereHas('order', function ($builder) use ($search) {
                    return $builder->where('phone', $search);
                });

            case 'name':
                return $builder->whereHas('order', function ($builder) use ($search) {
                    return $builder->where('name', $search);
                });
        }
    }


    private function prepareForGroupByDate(Builder $builder)
    {
        return $builder
            ->selectFields([
                'device_stat.datetime as _id',
                DB::raw("DATE_FORMAT(device_stat.datetime, '%d.%m.%Y') as _title")
            ]);
    }

    private function prepareForGroupByCountryId(Builder $query)
    {
        return $query->selectFields(['countries.id as _id', 'countries.title as _title'])
            ->leftJoin('countries', 'countries.id', '=', 'device_stat.country_id');
    }

    private function prepareForGroupByDeviceTypeId(Builder $query)
    {
        return $query->selectFields(['device_types.id as _id', 'device_types.title as _title'])
            ->leftJoin('device_types', 'device_types.id', '=', 'device_stat.device_type_id');
    }

    private function prepareForGroupByBrowserId(Builder $query)
    {
        return $query->selectFields(['browsers.id as _id', 'browsers.title as _title'])
            ->leftJoin('browsers', 'browsers.id', '=', 'device_stat.browser_id');
    }

    private function prepareForGroupByOsPlatformId(Builder $query)
    {
        return $query->selectFields(['os_platforms.id as _id', 'os_platforms.title as _title'])
            ->leftJoin('os_platforms', 'os_platforms.id', '=', 'device_stat.os_platform_id');
    }

    private function prepareForGroupByData1(Builder $query)
    {
        return $query->selectFields(['device_stat.data1 as _id', 'device_stat.data1 as _title']);
    }

    private function prepareForGroupByData2(Builder $query)
    {
        return $query->selectFields(['device_stat.data2 as _id', 'device_stat.data2 as _title']);
    }

    private function prepareForGroupByData3(Builder $query)
    {
        return $query->selectFields(['device_stat.data3 as _id', 'device_stat.data3 as _title']);
    }

    private function prepareForGroupByData4(Builder $query)
    {
        return $query->selectFields(['device_stat.data4 as _id', 'device_stat.data4 as _title']);
    }

    /**
     * Получение поля статистики для обнолвения статистики при изменении статусов лида
     *
     * @param Lead $lead
     * @return self
     */
    public function getFieldForLead(Lead $lead): self
    {
        $country_id = $lead['ip_country_id'] ?: $lead['country_id'];

        $datetime = date('Y-m-d H:00:00', strtotime($lead['created_at']));

        return self::where('datetime', $datetime)
            ->where('publisher_id', $lead['publisher_id'])
            ->where('flow_id', $lead['flow_id'])
            ->where('offer_id', $lead['offer_id'])
            ->where('device_type_id', $lead['device_type_id'])
            ->where('browser_id', $lead['browser_id'])
            ->where('os_platform_id', $lead['os_platform_id'])
            ->where('target_geo_country_id', $lead['country_id'])
            ->where('landing_id', $lead['landing_id'])
            ->where('transit_id', $lead['transit_id'])
            ->where('country_id', $country_id)
            ->where('currency_id', $lead['currency_id'])
            ->where('data1', $lead['data1'])
            ->where('data2', $lead['data2'])
            ->where('data3', $lead['data3'])
            ->where('data4', $lead['data4'])
            ->firstOrCreate([
                'datetime' => $datetime,
                'publisher_id' => $lead['publisher_id'],
                'flow_id' => $lead['flow_id'],
                'offer_id' => $lead['offer_id'],
                'device_type_id' => $lead['device_type_id'],
                'browser_id' => $lead['browser_id'],
                'os_platform_id' => $lead['os_platform_id'],
                'target_geo_country_id' => $lead['country_id'],
                'landing_id' => $lead['landing_id'],
                'transit_id' => $lead['transit_id'],
                'country_id' => $country_id,
                'currency_id' => $lead['currency_id'],
                'data1' => $lead['data1'],
                'data2' => $lead['data2'],
                'data3' => $lead['data3'],
                'data4' => $lead['data4']
            ]);
    }

    /**
     * Обновление статистики после определения лида рекламодетелю
     *
     * @param $lead
     */
    public static function onLeadCreated(Lead $lead)
    {
        $device_stat = (new self)->getFieldForLead($lead);

        DB::statement(
            "UPDATE `device_stat`
                        SET `held_count`    = (`held_count` + 1),
                            `onhold_payout` = (`onhold_payout` + {$lead['payout']})
                        WHERE `id`          = '{$device_stat['id']}';"
        );

        $lead->device_stat_id = $device_stat['id'];
        $lead->save();
    }

    public static function leadApprove(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `held_count`        = (`held_count` - 1),
					`onhold_payout`     = (`onhold_payout` - {$lead['payout']}),
					`approved_count`    = (`approved_count` + 1),
					`leads_payout`      = (`leads_payout` + {$lead['payout']}),
					`profit`            = (`profit` + {$lead['profit']})
				WHERE `id`              = '{$lead['device_stat_id']}';"
        );
    }

    public static function leadApproveAfterCancelled(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `cancelled_count`   = (`cancelled_count` - 1),
					`oncancel_payout`   = (`oncancel_payout` - {$lead['payout']}),
					`approved_count`    = (`approved_count` + 1),
					`leads_payout`      = (`leads_payout` + {$lead['payout']}),
					`profit`            = (`profit` + {$lead['profit']})
				WHERE `id`              = '{$lead['device_stat_id']}';"
        );
    }

    public static function leadApproveAfterTrashed(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `trashed_count`     = (`trashed_count` - 1),
					`ontrash_payout`    = (`ontrash_payout` - {$lead['payout']}),
					`approved_count`    = (`approved_count` + 1),
					`leads_payout`      = (`leads_payout` + {$lead['payout']}),
					`profit`            = (`profit` + {$lead['profit']})
				WHERE `id`              = '{$lead['device_stat_id']}';"
        );
    }

    public static function leadCancelled(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `held_count`        = (`held_count` - 1),
					`onhold_payout`     = (`onhold_payout` - {$lead['payout']}),
					`cancelled_count`   = (`cancelled_count` + 1),
					`oncancel_payout`   = (`oncancel_payout` + {$lead['payout']})
				WHERE `id`              = '{$lead['device_stat_id']}';"
        );
    }

    public static function updateOnLeadCancelAfterApproved(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `approved_count`    = (`approved_count` - 1),
					`leads_payout`      = (`leads_payout` - {$lead['payout']}),
					`profit`            = (`profit` - {$lead['profit']}),
					`cancelled_count`   = (`cancelled_count` + 1),
					`oncancel_payout`   = (`oncancel_payout` + {$lead['payout']})
				WHERE `id`              = '{$lead['device_stat_id']}';"
        );
    }

    public static function updateOnLeadCancelAfterTrashed(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `trashed_count`   = (`trashed_count`  - 1),
					`ontrash_payout`  = (`ontrash_payout` - {$lead['payout']}),
					`cancelled_count` = (`cancelled_count` + 1),
					`oncancel_payout` = (`oncancel_payout` + {$lead['payout']})
				WHERE `id`            = '{$lead['device_stat_id']}';"
        );
    }

    public static function leadTrash(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `held_count`        = (`held_count` - 1),
					`onhold_payout`     = (`onhold_payout` - {$lead['payout']}),
					`trashed_count`   	= (`trashed_count` + 1),
					`ontrash_payout`  	= (`ontrash_payout` + {$lead['payout']})
				WHERE `id`              = {$lead['device_stat_id']};"
        );
    }

    public static function leadTrashAfterCancelled(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `cancelled_count` = (`cancelled_count` - 1),
					`oncancel_payout` = (`oncancel_payout` - {$lead['payout']}),
					`trashed_count`   = (`trashed_count` + 1),
					`ontrash_payout`  = (`ontrash_payout` + {$lead['payout']})
				WHERE `id`            = '{$lead['device_stat_id']}';"
        );
    }

    public static function leadTrashAfterApproved(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `approved_count`   = (`approved_count`- 1),
					`leads_payout`     = (`leads_payout` - {$lead['payout']}),
				    `profit`           = (`profit` - {$lead['profit']}),
					`trashed_count`    = (`trashed_count` + 1),
					`ontrash_payout`   = (`ontrash_payout` + {$lead['payout']})
				WHERE `id`             = '{$lead['device_stat_id']}';"
        );
    }

    public static function leadRevertedAfterApproved(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `approved_count`   = (`approved_count`- 1),
					`leads_payout`     = (`leads_payout` - {$lead['payout']}),
				    `profit`           = (`profit` - {$lead['profit']})
				WHERE `id`             = '{$lead['device_stat_id']}';"
        );
    }

    public static function leadRevertedAfterCancelled(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `cancelled_count`   = (`cancelled_count`- 1),
					`oncancel_payout`   = (`oncancel_payout` - {$lead['payout']})
				WHERE `id`              = '{$lead['device_stat_id']}';"
        );
    }

    public static function leadRevertedAfterTrashed(Lead $lead)
    {
        if (!self::isLeadValid($lead)) {
            return;
        }

        DB::statement(
            "UPDATE `device_stat`
				SET `trashed_count`     = (`trashed_count`- 1),
					`ontrash_payout`    = (`ontrash_payout` - {$lead['payout']})
				WHERE `id`              = '{$lead['device_stat_id']}';"
        );
    }

    private static function isLeadValid(Lead $lead)
    {
        return !empty($lead['device_stat_id']);
//        if (empty($lead['device_stat_id'])) {
//            throw new \LogicException('Поле device_stat_id лида пустое.');
//        }
    }
}
