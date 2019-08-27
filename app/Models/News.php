<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\DynamicHiddenVisibleTrait;
use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
    Builder, Model, SoftDeletes
};
use App\Exceptions\User\UnknownUserRole;
use App\Models\Scopes\GlobalUserEnabledScope;
use App\Models\Traits\EloquentHashids;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @method $this whereOffers(array $offer_hashes = [])
 * @method $this haveOfferAccess()
 * @method $this published()
 * @method $this onlyMy(bool $only_my)
 */
class News extends AbstractEntity
{
    use SoftDeletes;
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;

    public const OFFER_EDITED = 'offer_edited';
    public const OFFER_STOPPED = 'offer_stopped';
    public const OFFER_CREATED = 'offer_created';
    public const PROMO_CREATED = 'promo_created';
    public const SYSTEM = 'system';
    public const STOCK = 'stock';

    protected $fillable = ['title', 'type', 'offer_id', 'body', 'author_id', 'published_at'];
    protected $hidden = ['id', 'offer_id', 'author_id', 'updated_at', 'deleted_at', 'has_direct_access', 'has_group_access'];
    public static $rules = [
        'title' => 'required|max:255',
        'body' => 'required|string',
        'type' => [
            'required',
            'in:' . self::OFFER_EDITED
            . ',' . self::OFFER_STOPPED
            . ',' . self::OFFER_CREATED
            . ',' . self::PROMO_CREATED
            . ',' . self::SYSTEM
            . ',' . self::STOCK
        ],
        'offer_id' => 'sometimes|exists:offers,id',
        'published_at' => 'required|date_format:Y-m-d H:i:s'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function setBodyAttribute(string $body)
    {
        $this->attributes['body'] = nl2br($body);
    }

    public static function getHashidEncodingValue(Model $model)
    {
        return $model->id;
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function scopeUserEnabled(Builder $builder)
    {
        /**
         * @var self $builder
         */
        $user = \Auth::user();

        switch ($user['role']) {
            case User::PUBLISHER:
                return $builder->haveOfferAccess()->published();

            case User::ADVERTISER:
                return $builder->haveOfferAccess()->published();

            case User::ADMINISTRATOR:
                return $builder;

            default:
                throw new UnknownUserRole($user['role']);
        }
    }

    public function scopeHaveOfferAccess(Builder $builder): Builder
    {
        $user = \Auth::user();

        switch ($user['role']) {
            case User::PUBLISHER:
                return $builder
                    ->where('offer_id', 0)
                    ->orWhereHas('offer', function (Builder $builder) use ($user) {
                        return $builder
                            ->withoutGlobalScope(GlobalUserEnabledScope::class)
                            ->addSelect([
                                DB::raw('
                                    (SELECT 1
                                        FROM `offer_publisher`
                                        WHERE `offer_id` = `offers`.`id`
                                        AND `publisher_id` = ' . $user['id'] . ') as `has_direct_access`'),
                                DB::raw('
                                    (SELECT 1
                                        FROM `offer_user_group`
                                        WHERE `offer_id` = `offers`.`id`
                                        AND `user_group_id` = ' . $user['group_id'] . ') as `has_group_access`')
                            ])
                            ->having('is_private', 0)
                            ->orHaving('has_direct_access', 1)
                            ->orHaving('has_group_access', 1);

                    });

            case User::ADVERTISER:
                return $builder
                    ->where('offer_id', 0)
                    ->orWhereHas('offer', function (Builder $builder) use ($user) {
                        return $builder
                            ->withoutGlobalScope(GlobalUserEnabledScope::class)
                            ->select([
                                DB::raw('
                                    (SELECT 1 
                                        FROM `offer_advertiser`
                                        WHERE `offer_id` = `offers`.`id` 
                                        AND `advertiser_id` = ' . $user['id'] . ') as `has_access`')
                            ])
                            ->having('is_private', 0)
                            ->orHaving('has_access', 1);
                    });
        }

        return $builder;
    }

    public function scopePublished(Builder $builder)
    {
        return $builder->where('published_at', '<=', Carbon::now()->toDateTimeString());
    }

    public function scopeOnlyMy(Builder $builder, bool $only_my = false)
    {
        $user = \Auth::user();
        if (!$only_my || \is_null($user) || !$user->isPublisher()) {
            return $builder;
        }

        return $builder
            ->where('news.offer_id', '!=', 0)
            ->orWhereExists(function (QueryBuilder $builder) use ($user) {
                $builder->select(DB::raw(1))
                    ->from('offers')
                    ->leftJoin('flows', 'flows.offer_id', '=', 'offers.id')
                    ->whereRaw('news.offer_id = offers.id')
                    ->where('flows.publisher_id', $user['id']);
            });
    }

    public function scopeWhereOffers(Builder $builder, array $offer_hashes = [])
    {
        return $builder->when($offer_hashes, function (Builder $builder) use ($offer_hashes) {
            $ids = getIdsByHashes($offer_hashes);
            $get_without_offer = \in_array('0', $offer_hashes);

            if ($get_without_offer) {
                $ids[] = 0;
            }

            if (\count($ids)) {
                return $builder->whereIn('offer_id', $ids);
            }
        });
    }
}
