<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\GlobalUserEnabledScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\EloquentHashids;

class CloakDomainPath extends AbstractEntity
{
    use SoftDeletes;
    use EloquentHashids;

    public const SAFEPAGE_STATUS = 'safepage';
    public const MONEYPAGE_STATUS = 'moneypage';
    public const MONEYPAGE_FOR_STATUS = 'moneypage_for';
    public const DATA1 = 'data1';
    public const DATA2 = 'data2';
    public const DATA3 = 'data3';
    public const DATA4 = 'data4';

    protected $fillable = [
        'user_id', 'domain_id', 'flow_id', 'path', 'status', 'is_cache_result', 'data_parameter', 'identifiers'
    ];
    protected $hidden = ['id', 'user_id', 'domain_id', 'flow_id', 'updated_at', 'deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function setPathAttribute($path): void
    {
        if (!starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $this->attributes['path'] = explode('?', $path)[0];
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }

    public function cloak()
    {
        return $this->hasOne(CloakDomainPathCloakSystem::class);
    }

    public function scopeUserEnabled(Builder $builder)
    {
        $user = \Auth::user();
        return $builder->where('user_id', $user['id']);
    }

    public function scopeWhereDomainHash(Builder $builder, ?string $domain_hash)
    {
        if (is_null($domain_hash)) {
            return $builder;
        }

        $domain_id = \Hashids::decode($domain_hash)[0] ?? false;
        if ($domain_id === false) {
            return $builder;
        }

        return $builder->where('domain_id', $domain_id);
    }

    public function scopeWhereDomain(Builder $builder, Domain $domain)
    {
        return $builder->where('domain_id', $domain->id);
    }

    public function scopeWherePath(Builder $builder, string $path)
    {
        return $builder->where('path', $path);
    }

    public function isSafepage()
    {
        return $this->status === self::SAFEPAGE_STATUS;
    }

    public function isMoneypageFor()
    {
        return $this->status === self::MONEYPAGE_FOR_STATUS;
    }
}
