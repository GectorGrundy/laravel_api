<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\GlobalUserEnabledScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\EloquentHashids;
use App\Models\Traits\DynamicHiddenVisibleTrait;

/**
 * @property Offer $offer
 * @method $this whereOffer(array $offer_hashes)
 * @method $this default()
 * @method $this availableForUser(User $user)
 */
class Target extends AbstractEntity
{
    use SoftDeletes;
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;

    public const CPA = 'CPA';
    public const CPL = 'CPL';

    public const EXTERNAL_LANDING = 'external';
    public const INTERNAL_LANDING = 'internal';

    protected $fillable = [
        'label', 'type', 'offer_id', 'locale_id', 'target_template_id', 'is_active', 'is_default', 'landing_type',
        'is_autoapprove', 'is_private'
    ];
    protected $hidden = [
        'id', 'type', 'offer_id', 'is_active', 'is_default', 'created_at', 'updated_at', 'deleted_at', 'pivot',
        'has_direct_access', 'has_group_access', 'is_autoapprove', 'target_template_id'
    ];
    protected $dates = ['deleted_at'];

    protected $appends = ['is_publisher_active'];

    public static $rules = [
        'label' => 'present|string|max:255',
        'target_template_id' => 'required|exists:target_templates,id',
        'offer_id' => 'required|exists:offers,id,deleted_at,NULL',
        'locale_id' => 'required|exists:locales,id',
        'is_active' => 'required|in:0,1',
        'is_default' => 'required|in:0,1',
        'is_private' => 'required|in:0,1',
        'is_autoapprove' => 'required|in:0,1',
        'type' => 'required|in:' . self::CPA . ',' . self::CPL,
    ];

    public function getIsPublisherActiveAttribute()
    {
        if ($this['is_private'] && !$this['has_direct_access'] && !$this['has_group_access']) {
            return 0;
        }
        return 1;
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function target_geo()
    {
        return $this->hasMany(TargetGeo::class);
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }

    public function landings()
    {
        return $this->hasMany(Landing::class);
    }

    public function transits()
    {
        return $this->hasMany(Transit::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function template()
    {
        return $this->belongsTo(TargetTemplate::class, 'target_template_id');
    }

    public function user_groups()
    {
        return $this->belongsToMany(
            UserGroup::class,
            'target_user_group',
            'target_id',
            'user_group_id'
        );
    }

    public function publishers()
    {
        return $this->belongsToMany(
            User::class,
            'target_publisher',
            'target_id',
            'publisher_id'
        );
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('is_active', 1);
    }

    public function scopeDefault(Builder $query)
    {
        return $query->where('is_default', 1);
    }

    public function scopeUserEnabled(Builder $query)
    {
        $user = \Auth::user();
        if (!$user->isAdmin()) {
            $query = $query->active();
        }
        switch ($user['role']) {
            case User::PUBLISHER:
                return $query->haveAccess($user);
        }

        return $query;
    }

    public function scopeHaveAccess(Builder $builder, ?User $user = null): Builder
    {

        $user = \is_null($user) ? \Auth::user() : $user;

        switch ($user['role']) {
            case User::PUBLISHER:
                $group_id = $user['group_id'];

                return $builder
                    ->distinct()
                    ->withoutGlobalScope(GlobalUserEnabledScope::class)
                    ->addSelect([
                        'targets.*',
                        \DB::raw('
                        (SELECT 1 
                            FROM `target_publisher`
                            WHERE `target_id` = `targets`.`id` 
                            AND `publisher_id` = ' . $user['id'] . ') as `has_direct_access`'),
                        \DB::raw('
                        (SELECT 1 
                            FROM `target_user_group`
                            WHERE `target_id` = `targets`.`id` 
                            AND `user_group_id` = ' . $group_id . ') as `has_group_access`')
                    ]);
        }

        return $builder;
    }

    public function scopeAvailableForUser(Builder $builder, User $user)
    {
        if (\is_null($user)) {
            return $builder;
        }

        switch ($user['role']) {
            case User::ADVERTISER:
            case User::SUPPORT:
                return $builder->active();

            case User::PUBLISHER:
                return $builder
                    ->active()
                    ->having('is_private', 0)
                    ->orHaving('has_direct_access', 1)
                    ->orHaving('has_group_access', 1);

            case User::ADMINISTRATOR:
                return $builder;
        }
    }

    public function getInfo($target_id)
    {
        return self::findOrFail($target_id);
    }

    public function scopeWhereOffer(Builder $builder, $offer_hashes)
    {
        if (\is_array($offer_hashes) && \count($offer_hashes) > 0) {
            $builder->whereIn('offer_id', getIdsByHashes($offer_hashes));
        }
        return $builder;
    }

    public function getDefaultForOffer($offer_id): self
    {
        return self::where(['offer_id' => $offer_id, 'is_default' => 1])->firstOrFail();
    }

    public function hasDefaultTargetGeo(int $except_id = 0)
    {
        return TargetGeo::where('target_id', $this['id'])
            ->default()
            ->when($except_id, function (Builder $builder) use ($except_id) {
                $builder->where('id', '!=', $except_id);
            })
            ->exists();
    }

    public function makeTargetGeoAsIsNotDefault(int $except_id = 0)
    {
        return TargetGeo::where('target_id', $this['id'])
            ->when($except_id, function (Builder $builder) use ($except_id) {
                $builder->where('id', '!=', $except_id);
            })
            ->update(['is_default' => 0]);
    }

    public function isAutoapprove()
    {
        return $this->is_autoapprove;
    }

    public static function getPermittedIdsForuser()
    {
        return self::get()->pluck('id')->toArray();
    }

    public function hasExternalLandings(): bool
    {
        return $this['landing_type'] === self::EXTERNAL_LANDING;
    }
}
