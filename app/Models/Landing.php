<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{
    Builder, SoftDeletes
};
use App\Models\Traits\EloquentHashids;
use App\Exceptions\User\UnknownUserRole;
use App\Models\Scopes\GlobalUserEnabledScope;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use App\Exceptions\Hashids\NotDecodedHashException;

/**
 * @method $this active()
 * @method $this whereOffer(array $offer_hashes)
 * @method $this search($search)
 */
class Landing extends AbstractEntity
{
    use SoftDeletes;
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;

    public const IMAGE_PATH = '/storage/images/landings/';
    public const COD = 'cod';

    protected $fillable = [
        'title', 'subdomain', 'offer_id', 'target_id', 'locale_id', 'is_private', 'is_mobile',
        'is_active', 'today_epc', 'yesterday_epc', 'week_epc', 'month_epc', 'today_cr', 'yesterday_cr', 'week_cr',
        'month_cr', 'is_responsive', 'is_advertiser_viewable', 'is_address_on_success',
        'is_email_on_success', 'is_custom_success', 'type', 'is_external', 'has_thumb',
        'is_back_action', 'is_back_call', 'is_vibrate_on_mobile',
    ];
    protected $hidden = [
        'id', 'offer_id', 'target_id', 'created_at', 'is_active', 'is_address_on_success',
        'is_advertiser_viewable', 'is_custom_success', 'is_email_on_success', 'updated_at',
        'deleted_at', 'pivot', 'is_external',
        'today_epc', 'yesterday_epc', 'week_epc', 'month_epc', 'today_cr', 'yesterday_cr', 'week_cr', 'month_cr',
    ];
    protected $dates = ['deleted_at'];
    protected $appends = ['thumb_path', 'cr', 'epc'];

    public static $rules = [
        'offer_id' => 'required|numeric|exists:offers,id',
        'target_id' => 'required|numeric|exists:targets,id',
        'locale_id' => 'required|numeric|exists:locales,id',
        'title' => 'required|string|max:255',
        'is_active' => 'required|in:0,1',
        'is_private' => 'required|in:0,1',
        'is_responsive' => 'required|in:0,1',
        'is_mobile' => 'required|in:0,1',
        'is_advertiser_viewable' => 'required|in:0,1',
        'is_address_on_success' => 'required_if:is_external,0|in:0,1',
        'is_email_on_success' => 'required_if:is_external,0|in:0,1',
        'is_custom_success' => 'required_if:is_external,0|in:0,1',
        'is_external' => 'required|in:0,1',
        'is_back_action' => 'required|in:0,1',
        'is_back_call' => 'required|in:0,1',
        'is_vibrate_on_mobile' => 'required|in:0,1',
    ];

    public function getEpcAttribute()
    {
        return $this->week_epc ?? 0;
    }

    public function getCrAttribute()
    {
        return $this->week_cr ?? 0;
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

    public function flow_selected_domain()
    {
        return $this->belongsToMany(Domain::class, 'flow_landing', 'landing_id', 'domain_id');
    }

    public function parked_domains()
    {
        return $this->hasMany(Domain::class, 'entity_id', 'id')->where('type', 'landing');
    }

    public function domains()
    {
        return $this->morphMany(Domain::class, 'entity');
    }

    public function domain()
    {
        return $this->morphOne(Domain::class, 'entity')->orderByRaw("FIELD(type, 'custom') DESC");
    }

    public function system_domain()
    {
        return $this
            ->morphOne(Domain::class, 'entity')
            ->where('type', Domain::SYSTEM_TYPE);
    }

    public function custom_domains()
    {
        return $this->morphMany(Domain::class, 'entity')->type(Domain::CUSTOM_TYPE);
    }

    public function publishers()
    {
        return $this->belongsToMany(User::class, 'landing_user', 'landing_id', 'publisher_id');
    }

    public function scopeUserHaveAccess(Builder $builder)
    {
        $user = \Auth::user();

        switch ($user['role']) {
            case User::PUBLISHER:
                $target_ids = Target::availableForUser($user)->get()->pluck('id')->toArray();

                $landing_ids = self::withoutGlobalScope(GlobalUserEnabledScope::class)
                    ->leftJoin('landing_user as lu', 'lu.landing_id', '=', 'landings.id')
                    ->where('lu.publisher_id', $user['id'])
                    ->orWhere('is_private', 0)
                    ->active()
                    ->get(['landings.id'])
                    ->pluck('id')
                    ->toArray();

                $builder->whereIn('landings.id', $landing_ids)->whereIn('target_id', $target_ids);
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
            $builder->whereIn('landings.offer_id', getIdsByHashes($offer_hashes));
        }
        return $builder;
    }

    /**
     * Скоуп для фильтрации по флагу is_mobile
     *
     * Внимание! Скоуп не просто фильтрует по полю is_mobile, он так же берет во внимание флаг is_responsive
     *
     * @param $builder
     * @param $is_mobile
     * @return mixed
     */
    public function scopeMobile(Builder $builder, $is_mobile)
    {
        if (\is_null($is_mobile)) {
            return $builder;
        }

        if ($is_mobile) {
            return $builder
                ->where('landings.is_mobile', 1)
                ->orWhere('landings.is_responsive', 1);
        }

        return $builder->where('landings.is_mobile', 0);
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('landings.is_active', 1);
    }

    /**
     * Скоуп для поиска по имени или по хэшу
     *
     * @param $builder
     * @param $search
     * @return mixed
     */
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

        $search = urldecode(trim($search));

        return $builder->where("landings.{$search_field}", 'like', "{$search}%");
    }

    public function scopeWithFlowLandingDomain(Builder $builder, $landing_id, $flow_id)
    {
        return $builder->with(['flow_selected_domain' => function ($builder) use ($landing_id, $flow_id) {
            return $builder->where('landing_id', $landing_id)->where('flow_id', $flow_id);
        }]);
    }

    public function getById(int $id, array $with = []): Landing
    {
        return self::with($with)->findOrFail($id);
    }

    public function getByHash(string $hash, array $with = []): Landing
    {
        return $this->getById($this->getIdFromHash($hash), $with);
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

    public function saveImage(string $image)
    {
        file_force_contents(public_path($this->getThumbPath()), file_get_contents($image));
    }

    public function remove()
    {
        $this->delete();
    }
}
