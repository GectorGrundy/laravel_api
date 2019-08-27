<?php
declare(strict_types=1);

namespace App\Models;

use DB;
use Hashids;
use Illuminate\Database\Eloquent\Builder;

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
class HourlyStat extends AbstractEntity
{
    protected $table = 'hourly_stat';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $hidden = ['landing_id', 'transit_id'];
    protected $appends = ['landing_unique_count'];

    public function getLandingUniqueCountAttribute()
    {
        if (isset($this->attributes['transit_landing_hosts'],
            $this->attributes['direct_landing_hosts'],
            $this->attributes['comeback_landing_hosts'],
            $this->attributes['noback_landing_hosts'])
        ) {
            return $this->attributes['landing_unique_count'] =
                $this->attributes['transit_landing_hosts']
                + $this->attributes['direct_landing_hosts']
                + $this->attributes['comeback_landing_hosts']
                + $this->attributes['noback_landing_hosts'];
        }
    }

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

    public function region()
    {
        return $this->belongsTo(Region::class);
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

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function scopeWhereUser(Builder $builder, $user_id, $user_role, $publisher_hashes = [])
    {
        switch ($user_role) {
            case 'administrator':
            case 'advertiser':
                if (\count($publisher_hashes) > 0) {
                    $builder->whereIn('hourly_stat.publisher_id', getIdsByHashes($publisher_hashes));
                }

                break;

            case 'publisher':
                $builder->where('hourly_stat.publisher_id', $user_id);
                break;
        }

        return $builder;
    }

    public function scopeWhereFlow(Builder $builder, $flows_hashes)
    {
        if (\is_array($flows_hashes) && \count($flows_hashes) > 0) {
            $builder->whereIn('hourly_stat.flow_id', getIdsByHashes($flows_hashes));
        }
        return $builder;
    }

    public function scopeWhereOffer(Builder $builder, $offers_hashes)
    {
        if (\is_array($offers_hashes) && \count($offers_hashes) > 0) {
            $builder->whereIn('hourly_stat.offer_id', getIdsByHashes($offers_hashes));
        }
        return $builder;
    }

    public function scopeWhereCountries(Builder $builder, $countries_ids)
    {
        if (\is_array($countries_ids) && \count($countries_ids) > 0) {
            $builder->whereIn('hourly_stat.country_id', $countries_ids);
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

    public function scopeWhereRegion(Builder $builder, $region_ids)
    {
        if (\is_array($region_ids) && \count($region_ids) > 0) {
            $builder->whereIn('hourly_stat.region_id', $region_ids);
        }
        return $builder;
    }

    public function scopeWhereTargetGeoCountry($query, $country_ids)
    {
        if (is_array($country_ids) && count($country_ids) > 0) {
            $query->whereIn('hourly_stat.target_geo_country_id', $country_ids);
        }

        return $query;
    }

    public function scopeWhereData1($query, $data1_list)
    {
        if (is_array($data1_list) && count($data1_list) > 0) {
            $query->whereIn('hourly_stat.data1', $data1_list);
        }

        return $query;
    }

    public function scopeWhereData2($query, $data2_list)
    {
        if (is_array($data2_list) && count($data2_list) > 0) {
            $query->whereIn('hourly_stat.data2', $data2_list);
        }

        return $query;
    }

    public function scopeWhereData3($query, $data3_list)
    {
        if (is_array($data3_list) && count($data3_list) > 0) {
            $query->whereIn('hourly_stat.data3', $data3_list);
        }

        return $query;
    }

    public function scopeWhereData4($query, $data4_list)
    {
        if (is_array($data4_list) && count($data4_list) > 0) {
            $query->whereIn('hourly_stat.data4', $data4_list);
        }

        return $query;
    }

    /**
     * Скоуп, которые добавляет поля, которые нужно добавить в результирующий набор
     *
     * @param $query
     * @param $select_fields
     * @return mixed
     */
    public function scopeSelectFields($query, $select_fields)
    {
        if (\is_array($select_fields) && \count($select_fields) > 0) {
            foreach ($select_fields AS $field) {
                $query->addSelect($field);
            }
        }

        return $query;
    }

    /**
     * Скоуп, который добавляет поля в результирующий набор, которые нужно просумировать
     *
     * @param $query
     * @param $sum_fields
     * @return mixed
     */
    public function scopeSumFields($query, $sum_fields)
    {
        if (is_array($sum_fields) && count($sum_fields) > 0) {
            foreach ($sum_fields AS $field) {
                $query->addSelect(DB::raw("SUM({$field}) AS {$field}"));
            }
        }

        return $query;
    }

    /**
     * Скоуп, который добавляет поля в результирующий набор, по которым нужно получить средние значения
     *
     * @param $query
     * @param $sum_fields
     * @return mixed
     */
    public function scopeAverageFields($query, $sum_fields)
    {
        if (is_array($sum_fields) && count($sum_fields) > 0) {
            foreach ($sum_fields AS $field) {
                $query->addSelect(DB::raw("ROUND(AVG({$field}), 2) AS {$field}"));
            }
        }

        return $query;
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

    public function scopePrepareForGroupBy($query, $group_by)
    {
        switch ($group_by) {
            case 'date':
                return $this->prepareForGroupByDate($query);

            case 'hour':
                return $this->prepareForGroupByHour($query);

            case 'transit_id':
                return $this->prepareForGroupByTransitId($query);

            case 'landing_id':
                return $this->prepareForGroupByLandingId($query);

            case 'country_id':
                return $this->prepareForGroupByCountryId($query);

            case 'region_id':
                return $this->prepareForGroupByRegionId($query);

            case 'city_id':
                return $this->prepareForGroupByCityId($query);

            case 'data1':
                return $this->prepareForGroupByData1($query);

            case 'data2':
                return $this->prepareForGroupByData2($query);

            case 'data3':
                return $this->prepareForGroupByData3($query);

            case 'data4':
                return $this->prepareForGroupByData4($query);
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

    private function prepareForGroupByDate(Builder $query)
    {
        return $query
            ->selectFields([
                'hourly_stat.datetime as _id',
                DB::raw("DATE_FORMAT(hourly_stat.datetime, '%d.%m.%Y') as _title")
            ]);
    }

    private function prepareForGroupByHour(Builder $query)
    {
        return $query
            ->selectFields([
                'hourly_stat.datetime as _id',
                DB::raw("DATE_FORMAT(hourly_stat.datetime, '%H') as _title")
            ]);
    }


    private function prepareForGroupByTransitId($query)
    {
        return $query->selectFields(['hourly_stat.transit_id as _id', 'transits.title as _title', 'transit_id'])
            ->with([
                'transit' => function ($q) {
                    $q->select('id', 'locale_id');

                },
                'transit.locale' => function ($q) {
                    $q->select('id', 'title', 'code');
                },
            ])
            ->leftJoin('transits', 'transits.id', '=', 'hourly_stat.transit_id');
    }

    private function prepareForGroupByLandingId($query)
    {
        return $query->selectFields(['landings.id as _id', 'landings.title as _title', 'landing_id'])
            ->with([
                'landing' => function ($q) {
                    $q->select('id', 'locale_id');

                },
                'landing.locale' => function ($q) {
                    $q->select('id', 'title', 'code');
                },
            ])
            ->leftJoin('landings', 'landings.id', '=', 'hourly_stat.landing_id');
    }

    private function prepareForGroupByCountryId($query)
    {
        return $query->selectFields(['countries.id as _id', 'countries.title as _title'])
            ->leftJoin('countries', 'countries.id', '=', 'hourly_stat.country_id');
    }

    private function prepareForGroupByRegionId($query)
    {
        return $query->selectFields(['regions.id as _id', 'regions.title as _title'])
            ->leftJoin('regions', 'regions.id', '=', 'hourly_stat.region_id');
    }

    private function prepareForGroupByCityId($query)
    {
        return $query->selectFields(['cities.id as _id', 'cities.title as _title'])
            ->leftJoin('cities', 'cities.id', '=', 'hourly_stat.city_id');
    }

    private function prepareForGroupByData1($query)
    {
        return $query->selectFields(['hourly_stat.data1 as _id', 'hourly_stat.data1 as _title']);
    }

    private function prepareForGroupByData2($query)
    {
        return $query->selectFields(['hourly_stat.data2 as _id', 'hourly_stat.data2 as _title']);
    }

    private function prepareForGroupByData3($query)
    {
        return $query->selectFields(['hourly_stat.data3 as _id', 'hourly_stat.data3 as _title']);
    }

    private function prepareForGroupByData4($query)
    {
        return $query->selectFields(['hourly_stat.data4 as _id', 'hourly_stat.data4 as _title']);
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
            ->where('transit_id', $lead['transit_id'])
            ->where('landing_id', $lead['landing_id'])
            ->where('target_geo_country_id', $lead['country_id'])
            ->where('country_id', $country_id)
            ->where('region_id', $lead['region_id'])
            ->where('city_id', $lead['city_id'])
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
                'transit_id' => $lead['transit_id'],
                'landing_id' => $lead['landing_id'],
                'target_geo_country_id' => $lead['country_id'],
                'country_id' => $country_id,
                'region_id' => $lead['region_id'],
                'city_id' => $lead['city_id'],
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
        $hourly_stat = (new self)->getFieldForLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
                        SET `held_count`    = (`held_count` + 1),
                            `onhold_payout` = (`onhold_payout` + {$lead['payout']})
                        WHERE `id`          = '{$hourly_stat['id']}';"
        );

        $lead->hourly_stat_id = $hourly_stat['id'];
        $lead->save();
    }

    public static function leadApprove(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `held_count`        = (`held_count` - 1),
					`onhold_payout`     = (`onhold_payout` - {$lead['payout']}),
					`approved_count`    = (`approved_count` + 1),
					`leads_payout`      = (`leads_payout` + {$lead['payout']}),
					`profit`            = (`profit` + {$lead['profit']})
				WHERE `id`              = '{$lead['hourly_stat_id']}';"
        );
    }

    public static function leadApproveAfterCancelled(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `cancelled_count`   = (`cancelled_count` - 1),
					`oncancel_payout`   = (`oncancel_payout` - {$lead['payout']}),
					`approved_count`    = (`approved_count` + 1),
					`leads_payout`      = (`leads_payout` + {$lead['payout']}),
					`profit`            = (`profit` + {$lead['profit']})
				WHERE `id`              = '{$lead['hourly_stat_id']}';"
        );
    }

    public static function leadApproveAfterTrashed(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `trashed_count`     = (`trashed_count` - 1),
					`ontrash_payout`    = (`ontrash_payout` - {$lead['payout']}),
					`approved_count`    = (`approved_count` + 1),
					`leads_payout`      = (`leads_payout` + {$lead['payout']}),
					`profit`            = (`profit` + {$lead['profit']})
				WHERE `id`              = '{$lead['hourly_stat_id']}';"
        );
    }

    public static function leadCancelled(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `held_count`        = (`held_count` - 1),
					`onhold_payout`     = (`onhold_payout` - {$lead['payout']}),
					`cancelled_count`   = (`cancelled_count` + 1),
					`oncancel_payout`   = (`oncancel_payout` + {$lead['payout']})
				WHERE `id`              = '{$lead['hourly_stat_id']}';"
        );
    }

    public static function updateOnLeadCancelAfterApproved(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `approved_count`    = (`approved_count` - 1),
					`leads_payout`      = (`leads_payout` - {$lead['payout']}),
					`profit`            = (`profit` - {$lead['profit']}),
					`cancelled_count`   = (`cancelled_count` + 1),
					`oncancel_payout`   = (`oncancel_payout` + {$lead['payout']})
				WHERE `id`              = '{$lead['hourly_stat_id']}';"
        );
    }

    public static function updateOnLeadCancelAfterTrashed(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `trashed_count`   = (`trashed_count`  - 1),
					`ontrash_payout`  = (`ontrash_payout` - {$lead['payout']}),
					`cancelled_count` = (`cancelled_count` + 1),
					`oncancel_payout` = (`oncancel_payout` + {$lead['payout']})
				WHERE `id`            = '{$lead['hourly_stat_id']}';"
        );
    }

    public static function leadTrash(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `held_count`        = (`held_count` - 1),
					`onhold_payout`     = (`onhold_payout` - {$lead['payout']}),
					`trashed_count`   	= (`trashed_count` + 1),
					`ontrash_payout`  	= (`ontrash_payout` + {$lead['payout']})
				WHERE `id`              = {$lead['hourly_stat_id']};"
        );
    }

    public static function leadTrashAfterCancelled(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `cancelled_count` = (`cancelled_count` - 1),
					`oncancel_payout` = (`oncancel_payout` - {$lead['payout']}),
					`trashed_count`   = (`trashed_count` + 1),
					`ontrash_payout`  = (`ontrash_payout` + {$lead['payout']})
				WHERE `id`            = '{$lead['hourly_stat_id']}';"
        );
    }

    public static function leadTrashAfterApproved(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `approved_count`   = (`approved_count`- 1),
					`leads_payout`     = (`leads_payout` - {$lead['payout']}),
				    `profit`           = (`profit` - {$lead['profit']}),
					`trashed_count`    = (`trashed_count` + 1),
					`ontrash_payout`   = (`ontrash_payout` + {$lead['payout']})
				WHERE `id`             = '{$lead['hourly_stat_id']}';"
        );
    }

    public static function leadRevertedAfterApproved(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `approved_count`   = (`approved_count`- 1),
					`leads_payout`     = (`leads_payout` - {$lead['payout']}),
				    `profit`           = (`profit` - {$lead['profit']})
				WHERE `id`             = '{$lead['hourly_stat_id']}';"
        );
    }

    public static function leadRevertedAfterCancelled(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `cancelled_count`   = (`cancelled_count`- 1),
					`oncancel_payout`   = (`oncancel_payout` - {$lead['payout']})
				WHERE `id`              = '{$lead['hourly_stat_id']}';"
        );
    }

    public static function leadRevertedAfterTrashed(Lead $lead)
    {
        self::validateLead($lead);

        DB::statement(
            "UPDATE `hourly_stat`
				SET `trashed_count`     = (`trashed_count`- 1),
					`ontrash_payout`    = (`ontrash_payout` - {$lead['payout']})
				WHERE `id`              = '{$lead['hourly_stat_id']}';"
        );
    }

    private static function validateLead(Lead $lead)
    {
        if (empty($lead['hourly_stat_id'])) {
            throw new \LogicException('Поле hourly_stat_id лида пустое.');
        }
    }
}
