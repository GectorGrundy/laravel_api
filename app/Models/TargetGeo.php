<?php
declare(strict_types=1);

namespace App\Models;

use App\Events\TargetGeo\TargetGeoDeleted;
use App\Models\Scopes\GlobalUserEnabledScope;
use App\Models\Traits\HashDecoder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use App\Models\Traits\EloquentHashids;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @method $this default()
 */
class TargetGeo extends AbstractEntity
{
    use SoftDeletes;
    use DynamicHiddenVisibleTrait;
    use EloquentHashids;
    use HashDecoder;

    public const RULE_WEIGHT_SORT = 'weight';
    public const RULE_PRIOITY_SORT = 'priority';

    protected $fillable = [
        'offer_id', 'target_id', 'country_id', 'payout_currency_id', 'price_currency_id', 'payout',
        'hold_time', 'price', 'old_price', 'is_default', 'is_active', 'target_geo_rule_sort_type',
        'today_epc', 'yesterday_epc', 'week_epc', 'month_epc', 'today_cr',
        'yesterday_cr', 'week_cr', 'month_cr',
    ];
    protected $hidden = [
        'id', 'offer_id', 'target_id', 'hold_time', 'is_default', 'is_active',
        'target_geo_rule_sort_type', 'created_at',
        'updated_at', 'deleted_at', 'today_epc', 'yesterday_epc', 'week_epc', 'month_epc', 'today_cr',
        'yesterday_cr', 'week_cr', 'month_cr',
    ];
    public $advertiser_hidden = [
        'id', 'offer_id', 'target_id', 'hold_time', 'is_default', 'is_active',
        'target_geo_rule_sort_type', 'created_at',
        'updated_at', 'deleted_at', 'payout',
    ];
    protected $dates = ['deleted_at'];
    public $timestamps = false;
    public $table = 'target_geo';
    public static $rules = [
        'offer_id' => 'required|exists:offers,id,deleted_at,NULL',
        'target_id' => 'required|exists:targets,id,deleted_at,NULL',
        'country_id' => 'required|exists:countries,id',
        'price_currency_id' => 'required|exists:currencies,id',
        'price' => 'required|numeric|min:0',
        'old_price' => 'required|numeric|min:0',
        'hold_time' => 'required|numeric|min:0',
        'is_default' => 'required|in:0,1',
        'is_active' => 'required|in:0,1',
    ];
    protected $appends = ['cr', 'epc'];

    public function getEpcAttribute()
    {
        return $this->week_epc ?? 0;
    }

    public function getCrAttribute()
    {
        return $this->week_cr ?? 0;
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    // @todo Зачем?
    public function setCountryIdAttribute($country_id)
    {
        $this->attributes['country_id'] = $country_id;
    }

    public function target()
    {
        return $this->belongsTo(Target::class, 'target_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function price_currency()
    {
        return $this->belongsTo(Currency::class, 'price_currency_id');
    }

    public function payout_currency()
    {
        return $this->belongsTo(Currency::class, 'payout_currency_id');
    }

    public function target_geo_rules()
    {
        return $this->hasMany(TargetGeoRule::class);
    }

    // alias
    public function rules()
    {
        return $this->hasMany(TargetGeoRule::class);
    }

    public function fallback_target_geo_rule()
    {
        return $this->hasOne(TargetGeoRule::class)->where('is_fallback', 1);
    }

    public function publisher_target_geo()
    {
        return $this->hasOne(PublisherTargetGeo::class, 'id', 'target_geo_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function user_group_target_geo()
    {
        return $this->hasMany(UserGroupTargetGeo::class);
    }

    public function integration()
    {
        return $this->hasOne(TargetGeoIntegration::class);
    }

    public function scopeDefault(Builder $query)
    {
        return $query->where('is_default', 1);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeUserEnabled(Builder $query)
    {
        $user = \Auth::user();
        if (!$user->isAdmin()) {
            return $query->active();
        }

        return $query;
    }

    public function getListByTargetId(int $target_id, array $with = [], int $publisher_id = 0): Collection
    {
        $target_geo_list = self::with($with)->where('target_id', $target_id)->get();

        if (\is_null($target_geo_list)) {
            throw new ModelNotFoundException('Failed to get target geo info');
        }

        if ($publisher_id > 0) {
            (new self())->replaceCustomStakes($target_geo_list, $publisher_id);
        }

        return $target_geo_list;
    }

    public function getByTargetAndCountry(int $target_id, int $country_id, int $publisher_id = 0): TargetGeo
    {
        $target_geo = self::where('target_id', $target_id)
            ->where('country_id', $country_id)
            ->firstOrFail();

        if ($publisher_id > 0) {
            (new self())->replaceCustomStakes(collect([$target_geo]), $publisher_id);
        }

        return $target_geo;
    }

    /**
     * Получение кастомных параметррв гео целей для паблишера.
     * Если такие есть - подстановка их вместо оригинальных параметров гео цели
     *
     * @param $target_geo_list
     * @param $publisher_id
     * @return mixed
     */
    public function replaceCustomStakes(Collection $target_geo_list, int $publisher_id)
    {
        $custom_stakes = $this->getUserGroupStakes($target_geo_list, $publisher_id);
        foreach ($target_geo_list AS $target_geo) {

            $custom_stake = UserGroupTargetGeo::findStakeByTargetGeo($custom_stakes, $target_geo);

            if (!\is_null($custom_stake)) {
                // Заменяем значения гео цели кастомными для паблишера
                $this->replaceByCustomStake($custom_stake, $target_geo);
            }
        }

        return $target_geo_list;
    }

    private function replaceByCustomStake(UserGroupTargetGeo $user_group_target_geo, TargetGeo $target_geo): void
    {
        $target_geo->payout = $user_group_target_geo['payout'];
        $target_geo->payout_currency_id = $user_group_target_geo['currency_id'];
        $target_geo->today_epc = $user_group_target_geo['today_epc'];
        $target_geo->today_cr = $user_group_target_geo['today_cr'];
        $target_geo->yesterday_epc = $user_group_target_geo['yesterday_epc'];
        $target_geo->yesterday_cr = $user_group_target_geo['yesterday_cr'];
        $target_geo->week_epc = $user_group_target_geo['week_epc'];
        $target_geo->week_cr = $user_group_target_geo['week_cr'];
        $target_geo->month_epc = $user_group_target_geo['month_epc'];
        $target_geo->month_cr = $user_group_target_geo['month_cr'];

    }

    private function getUserGroupStakes(Collection $target_geo_list, int $publisher_id)
    {
        $ids = $target_geo_list->pluck('id')->toArray();
        $publisher = Publisher::find($publisher_id);

        return UserGroupTargetGeo::whereGroup((int)$publisher['group_id'])
            ->whereTargetGeo($ids)
            ->get();
    }

    public function getById(int $id, array $with = [], int $publisher_id = 0): self
    {
        $target_geo = self::with($with)->findOrFail($id);

        if ($publisher_id > 0) {
            (new self())->replaceCustomStakes(collect([$target_geo]), $publisher_id);
        }

        return $target_geo;
    }

    public function getByHash(string $hash, array $with = [], int $publisher_id = 0): self
    {
        $id = $this->getIdByHash($hash);

        return $this->getById($id, $with, $publisher_id);
    }

    public function getPublisherCurrencyId(): int
    {
        return (int)$this['payout_currency_id'];
    }

    public function remove()
    {
        $this->delete();

        event(new TargetGeoDeleted($this));
    }

    public function isRedirect(): bool
    {
        return $this->integration['integration_type'] === TargetGeoIntegration::REDIRECT;
    }
}
