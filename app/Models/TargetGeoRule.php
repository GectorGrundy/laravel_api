<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Collection;
use App\Models\Scopes\GlobalUserEnabledScope;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use Illuminate\Database\Eloquent\{
    Builder, Model, Relations\BelongsTo, SoftDeletes
};

class TargetGeoRule extends AbstractEntity
{
    use SoftDeletes;
    use DynamicHiddenVisibleTrait;

    protected $fillable = [
        'integration_id', 'target_geo_id', 'advertiser_id', 'charge', 'currency_id', 'priority', 'limit', 'weight',
        'is_fallback', 'integration_data',
    ];
    protected $hidden = [
        'id', 'target_geo_id', 'is_fallback', 'integration_id', 'advertiser_id', 'pivot', 'weight', 'limit', 'priority',
        'integration_data', 'integration_data_array', 'today_leads_count', 'created_at', 'updated_at', 'deleted_at',
    ];
    protected $dates = ['deleted_at'];
    protected $appends = ['integration_data_array', 'today_leads_count'];

    public static $rules = [
        'integration_id' => 'required|exists:integrations,id,deleted_at,NULL',
        'target_geo_id' => 'required|exists:target_geo,id,deleted_at,NULL',
        'limit' => 'required|numeric|min:0',
        'weight' => 'required|numeric|min:0',
        'integration_data' => 'required|json_object',
        'is_fallback' => 'required|in:0,1',
        'charge' => 'required|numeric|min:0',
    ];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function scopeUserEnabled(Builder $query)
    {
        $user = \Auth::user();

        if ($user->isPublisher()) {
            $query->where(\DB::raw('1'), '>=', 1);

        } else if ($user->isAdvertiser()) {
            $query->where('advertiser_id', $user['id']);
        }

        return $query;
    }

    public function getIntegrationDataArrayAttribute()
    {
        if (isset($this->attributes['integration_data'])) {
            return json_decode($this->attributes['integration_data'], true);
        }
    }

    public function getTodayLeadsCountAttribute(): int
    {
        return $this->getTodayLeadsCount($this->attributes['id']);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function target_geo(): BelongsTo
    {
        return $this->belongsTo(TargetGeo::class);
    }

    public function scopeWithoutFallback(Builder $query)
    {
        return $query->where('is_fallback', 0);
    }

    public function scopeTargetGeo(Builder $query, $target_geo_id)
    {
        return $query->when($target_geo_id, function (Builder $builder) use ($target_geo_id) {
            return $builder->where('target_geo_id', $target_geo_id);
        });
    }

    public function today_stat()
    {
        return $this->hasOne(TargetGeoRuleStat::class, 'target_geo_rule_id', 'id')->today();
    }

    public function canHaveLeadByLimit(): bool
    {
        return $this->limit === 0 || $this->limit > $this->getTodayLeadsCount($this->id);
    }

    public function getTodayLeadsCount(int $target_geo_rule_id): int
    {
        return \DB::table('target_geo_rule_leads_stat')
                ->where('target_geo_rule_id', $target_geo_rule_id)
                ->where('date', date('Y-m-d', time()))
                ->first()
                ->leads_count ?? 0;
    }

    public function rejectRulesByLimit(Collection $target_geo_rules): Collection
    {
        $limited_rules = collect();
        foreach ($target_geo_rules as $target_geo_rule) {

            if ($target_geo_rule->canHaveLeadByLimit()) {
                $limited_rules->push($target_geo_rule);
            }
        }

        return $limited_rules;
    }
}
