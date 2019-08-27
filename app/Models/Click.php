<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\EloquentHashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @property Flow flow
 * @property TargetGeo target_geo
 * @property Landing landing
 */
class Click extends AbstractEntity
{
    use EloquentHashids;

    protected $fillable = [
        'advertiser_id', 'advertiser_payout', 'advertiser_currency_id', 'target_geo_id', 'country_id',
        'region_id', 'city_id', 'landing_id', 'transit_id', 'flow_id', 'ip_country_id', 'domain_id', 'browser_id',
        'os_platform_id', 'device_type_id', 'transit_traffic_type', 'browser_locale', 'browser', 'is_extra_flow',
        'ip', 'ips', 'data1', 'data2', 'data3', 'data4', 'clickid', 's_id', 'user_agent', 'referer', 'initialized_at',
    ];
    protected $dates = ['initialized_at'];

    public static function getByHash(string $hash, array $with = []): self
    {
        $click_id = \Hashids::decode($hash)[0] ?? 0;
        if (empty($click_id)) {
            throw new ModelNotFoundException('Click');
        }

        return self::with($with)->where('hash', $hash)->firstOrFail();
    }

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }

    public function target_geo()
    {
        return $this->belongsTo(TargetGeo::class);
    }
}
