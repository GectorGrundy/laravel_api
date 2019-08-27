<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\EloquentHashids;
use App\Exceptions\User\UnknownUserRole;
use App\Models\Scopes\GlobalUserEnabledScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use App\Exceptions\Hashids\NotDecodedHashException;

/**
 * @method $this active()
 * @method $this whereOffer(array $offer_hashes)
 * @method $this search($search)
 */
class Transit extends AbstractEntity
{
    use SoftDeletes;
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;

    public const IMAGE_PATH = '/storage/images/transits/';

    protected $fillable = [
        'title', 'subdomain', 'offer_id', 'target_id', 'locale_id', 'is_private', 'is_mobile',
        'is_active', 'today_ctr', 'yesterday_ctr', 'week_ctr', 'month_ctr', 'is_responsive', 'is_advertiser_viewable',
    ];
    protected $hidden = [
        'id', 'is_active', 'is_advertiser_viewable', 'offer_id', 'target_id', 'created_at',
        'updated_at', 'deleted_at', 'pivot', 'today_ctr', 'yesterday_ctr', 'week_ctr', 'month_ctr',
    ];
    protected $dates = ['deleted_at'];
    protected $appends = ['thumb_path', 'ctr'];

    public static $rules = [
        'offer_id' => 'required|exists:offers,id',
        'target_id' => 'required|exists:targets,id',
        'locale_id' => 'required|exists:locales,id',
        'title' => 'required|max:255',
        'is_active' => 'required|in:0,1',
        'is_private' => 'required|in:0,1',
        'is_responsive' => 'required|in:0,1',
        'is_mobile' => 'required|in:0,1',
        'is_advertiser_viewable' => 'required|in:0,1',
    ];

    public function getCtrAttribute()
    {
        return $this->week_ctr ?? 0;
    }

    public function getThumbPathAttribute(): string
    {
        return $this->getThumbPath();
    }

    public function getThumbPath(): string
    {
        return self::IMAGE_PATH . $this->getAttribute('hash') . '.png';
    }

    public function target()
    {
        return $this->belongsTo(Target::class);
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function publishers()
    {
        return $this->belongsToMany(User::class, 'transit_user', 'transit_id', 'publisher_id');
    }

    public function parked_domains()
    {
        return $this->domains()->where('user_id', '>', 0);
    }

    public function domains()
    {
        return $this->morphMany(Domain::class, 'entity');
    }

    public function domain()
    {
        return $this->morphOne(Domain::class, 'entity');
    }

    public function system_domain()
    {
        return $this->morphOne(Domain::class, 'entity')
            ->where('type', Domain::SYSTEM_TYPE);
    }

    public function custom_domains()
    {
        return $this->morphMany(Domain::class, 'entity')->type(Domain::CUSTOM_TYPE);
    }

    public function flow_selected_domain()
    {
        return $this->belongsToMany(Domain::class, 'flow_transit', 'transit_id', 'domain_id');
    }

    public function scopeUserHaveAccess(Builder $builder)
    {
        $user = \Auth::user();

        switch ($user['role']) {
            case User::PUBLISHER:

                $target_ids = Target::availableForUser(\Auth::user())->get()->pluck('id')->toArray();

                $transit_ids = self::withoutGlobalScope(GlobalUserEnabledScope::class)
                    ->leftJoin('transit_user as tu', 'tu.transit_id', '=', 'transits.id')
                    ->where('tu.publisher_id', $user['id'])
                    ->orWhere('is_private', 0)
                    ->active()
                    ->get(['transits.id'])
                    ->pluck('id')->toArray();

                $builder->whereIn('transits.id', $transit_ids)->whereIn('target_id', $target_ids);
                break;

            case User::ADVERTISER:

                $builder->where('is_advertiser_viewable', 1)->active();
                break;
        }

        return $builder;
    }

    public function scopeWhereOffer(Builder $builder, $offer_hashes)
    {
        if (\is_array($offer_hashes) && \count($offer_hashes) > 0) {
            $builder->whereIn('transits.offer_id', rejectEmpty(getIdsByHashes($offer_hashes)));
        }
        return $builder;
    }

    public function scopeMobile(Builder $builder, $is_mobile)
    {
        if (\is_null($is_mobile)) {
            return $builder;
        }

        if ($is_mobile) {
            return $builder
                ->where('transits.is_mobile', 1)
                ->orWhere('transits.is_responsive', 1);
        }

        return $builder->where('transits.is_mobile', 0);
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('transits.is_active', 1);
    }

    public function scopeSearch(Builder $builder, $search)
    {
        if (empty($search)) {
            return $builder;
        }

        if (mb_substr($search, 0, 5) === 'hash:') {
            $search_field = 'hash';
            $search = mb_substr($search, 5);

        } else {
            $search_field = 'title';
        }

        $search = urldecode($search);

        return $builder->where("transits.{$search_field}", 'like', "%{$search}%");
    }

    public function scopeWithFlowTransitDomain(Builder $builder, $transit_id, $flow_id)
    {
        return $builder->with(['flow_selected_domain' => function ($q) use ($transit_id, $flow_id) {

            return $q->where('flow_transit.transit_id', $transit_id)
                ->where('flow_transit.flow_id', $flow_id);
        }]);
    }

    public function getInfo(int $id, array $relations = []): Transit
    {
        return self::with($relations)->findOrFail($id);
    }

    public function getByHash(string $hash, array $relations = []): Transit
    {
        $id = $this->getIdFromHash($hash);

        return $this->getInfo($id, $relations);
    }

    public function saveImage(string $image)
    {
        file_force_contents(public_path($this->getThumbPath()), file_get_contents($image));
    }

    public function getIdsFromHashes(array $hashes): array
    {
        $ids = [];
        foreach ($hashes as $hash) {
            $ids[] = $this->getIdFromHash($hash);
        }

        return $ids;
    }

    public function getIdFromHash(string $hash): int
    {
        $decoded_data = \Hashids::decode($hash);
        if (\count($decoded_data) < 1) {
            throw new NotDecodedHashException();
        }

        return (int)$decoded_data[0];
    }

    public function remove()
    {
        $this->delete();
    }
}
