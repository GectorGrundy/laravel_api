<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\EloquentHashids;

/**
 * @method $this doesntHaveLead()
 */
class TempLead extends AbstractEntity
{
    use EloquentHashids;
    use SoftDeletes;

    protected $fillable = [
        'target_geo_id', 'lead_id', 'flow_id', 'transit_id', 'landing_id', 'domain_id', 'name', 'phone',
        'ip', 'ips', 'data1', 'data2', 'data3', 'data4', 'clickid', 's_id', 'user_agent', 'referer',
        'transit_traffic_type', 'initialized_at', 'comment', 'products', 'ip_country_id', 'region_id', 'city_id',
        'is_extra_flow', 'browser_locale', 'session_id'
    ];
    protected $dates = ['initialized_at'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function target_geo()
    {
        return $this->belongsTo(TargetGeo::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

    public function scopeDoesntHaveLead(Builder $builder)
    {
        return $builder->where('lead_id', 0);
    }

    public function scopeSession(Builder $builder, string $s_id)
    {
        return $builder->where('s_id', $s_id);
    }

    public static function closeBySID(string $s_id, int $except = 0)
    {
        self::session($s_id)->where('id', '!=', $except)->delete();
    }
}
