<?php
declare(strict_types=1);

namespace App\Models;

use Cache;
use App\Models\Scopes\GlobalUserEnabledScope;
use App\Exceptions\Domain\IncorrectEntityType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Traits\EloquentHashids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\DynamicHiddenVisibleTrait;

/**
 * @method $this landing()
 * @method $this system()
 * @method $this type()
 * @method $this active()
 * @method $this whereEntity(int $entity_id)
 * @method $this entityTypes(array $types)
 */
class Domain extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;
    use EloquentHashids;
    use SoftDeletes;

    public const DEFAULT_DONOR_CHARSET = 'UTF-8';

    public const TDS_ENTITY_TYPE = 'tds';
    public const TRANSIT_ENTITY_TYPE = 'transit';
    public const LANDING_ENTITY_TYPE = 'landing';
    public const FLOW_ENTITY_TYPE = 'flow';
    public const REDIRECT_ENTITY_TYPE = 'redirect';

    public const CUSTOM_TYPE = 'custom';
    public const PARKED_TYPE = 'parked';
    public const SYSTEM_TYPE = 'system';

    protected $fillable = [
        'domain', 'type', 'user_id', 'entity_type', 'entity_id', 'fallback_flow_id', 'donor_url', 'donor_charset',
        'realpath', 'is_active', 'is_subdomain', 'is_public', 'is_https'
    ];
    protected $hidden = [
        'id', 'is_https', 'user_id', 'realpath', 'entity_id', 'fallback_flow_id', 'updated_at', 'deleted_at'
    ];
    protected $dates = ['deleted_at'];
    protected $appends = ['is_custom', 'host', 'is_new_cloaking'];

    public function setDonorUrlAttribute($donor_url): void
    {
        if (empty($donor_url)) {
            $this->attributes['donor_url'] = '';
            return;
        }

        $donor_url = str_replace('www.', '', $donor_url);
        $url_info = parse_url($donor_url);

        $this->attributes['donor_url'] = $url_info['scheme'] . '://' . $url_info['host'];
    }

    public function getIsCustomAttribute(): bool
    {
        return $this->getAttribute('type') === self::CUSTOM_TYPE;
    }

    public function getHostAttribute(): string
    {
        return ($this['is_https'] ? 'https' : 'http') . '://' . $this->getAttribute('domain');
    }

    public function getIsNewCloakingAttribute(): int
    {
        return (int)$this->isCloaked();
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function flow()
    {
        return $this->belongsTo(Flow::class, 'fallback_flow_id', 'id');
    }

    public function paths()
    {
        return $this->hasMany(CloakDomainPath::class);
    }

    public function replacements()
    {
        return $this->hasMany(DomainReplacement::class);
    }

    public function scopeType(Builder $query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeEntityTypes(Builder $query, $types)
    {
        return $query->when($types, function (Builder $query) use ($types) {
            return $query->whereIn('entity_type', $types);
        });
    }

    public function scopeTds(Builder $query)
    {
        return $query->entityTypes([self::TDS_ENTITY_TYPE]);
    }

    public function scopeRedirect(Builder $query)
    {
        return $query->entityTypes([self::REDIRECT_ENTITY_TYPE]);
    }

    public function scopeLanding(Builder $query)
    {
        return $query->entityTypes([self::LANDING_ENTITY_TYPE]);
    }

    public function scopeTransit(Builder $query)
    {
        return $query->entityTypes([self::TRANSIT_ENTITY_TYPE]);
    }

    public function scopeSystem(Builder $query)
    {
        return $query->type(self::SYSTEM_TYPE);
    }

    public function scopeWhereEntity(Builder $query, int $entity_id)
    {
        return $query->where('entity_id', $entity_id);
    }

    public function scopeService(Builder $query)
    {
        return $query->system()->where('entity_id', 0);
    }

    public function isService()
    {
        return $this->entity_id === 0 && $this->type === self::SYSTEM_TYPE;
    }

    public function isParked()
    {
        return $this->type === self::PARKED_TYPE;
    }

    public function isCloaked()
    {
        return $this->isParked() && !empty($this->donor_url);
    }

    public function isTransit()
    {
        return $this->entity_type === self::TRANSIT_ENTITY_TYPE;
    }

    public function isLanding()
    {
        return $this->entity_type === self::LANDING_ENTITY_TYPE;
    }

    public function isTds()
    {
        return $this->entity_type === self::TDS_ENTITY_TYPE;
    }

    public function isRedirect()
    {
        return $this->entity_type === self::REDIRECT_ENTITY_TYPE;
    }

    public function isSystem()
    {
        return $this->getAttribute('type') === self::SYSTEM_TYPE;
    }

    public function getById(int $id, array $with = [])
    {
        return self::with($with)->findOrFail($id);
    }

    public static function getByDomain(string $domain, array $with = []): self
    {
        $key = __CLASS__ . __METHOD__ . serialize(func_get_args());

        return Cache::get($key, function () use ($domain, $with, $key) {
            $domain = self::with($with)->where('domain', $domain)->firstOrFail();

            Cache::put($key, $domain, 2);
            return $domain;
        });
    }

    public function getActiveTdsDomain(): string
    {
        return self::tds()->system()->firstOrFail()->domain;
    }

    public function getSystemTransitDomain(): string
    {
        return self::transit()->system()->firstOrFail()->domain;
    }

    public function getSystemLandingDomain()
    {
        return self::landing()->system()->firstOrFail()->domain;
    }

    public function hasSymlink()
    {
        return !empty($this->getAttribute('realpath'));
    }

    public static function getSymlink(Domain $domain, bool $original = false)
    {
        if ($domain->entity_type !== 'landing' && $domain->entity_type !== 'transit') {
            throw new IncorrectEntityType();
        }

        $domain_name = $original ? $domain->getOriginal('domain') : $domain->domain;

        return config('env.domains_path') . '/' . $domain_name;
    }

    public static function getDefaultTds(): self
    {
        return self::tds()->firstOrFail();
    }

    public static function getDefaultRedirect(): self
    {
        return self::redirect()->firstOrFail();
    }

    public static function getLandingSystem(int $landing_id)
    {
        return self::where('entity_id', $landing_id)->landing()->system()->firstOrFail();
    }

    public static function getTransitSystem(int $transit_id)
    {
        return self::where('entity_id', $transit_id)->transit()->system()->firstOrFail();
    }

    public function scopeUserEnabled(Builder $builder)
    {
        $user = \Auth::user();
        if ($user->isAdmin()) {
            return $builder;
        }

        return $builder->whereIn('user_id', [$user['id'], 0])->active();
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('is_active', 1);
    }
}
