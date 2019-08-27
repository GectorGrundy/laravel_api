<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasTranslations;
use DB;
use Elasticsearch\Client;
use App\Classes\ElasticSchema;
use Illuminate\Support\Collection;
use App\Models\Traits\EloquentHashids;
use App\Exceptions\User\UnknownUserRole;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Scopes\AlreadyAddedOfferScope;
use App\Models\Scopes\GlobalUserEnabledScope;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @warning: There are observers on this model. To see them go to EventServiceProvider
 * @method $this search(string $search)
 * @method $this onlyMy(User $user, ?int $only_my)
 * @method $this haveAccess(Publisher $publisher)
 * @method $this isActiveForPublisher(User $publisher)
 * @method $this whereLabels(?array $labels)
 * @method $this availableForUser(User $user)
 * @method $this excludeArchived()
 * @method $this active()
 * @method $this categories(?array $category_ids)
 * @method $this sources(?array $source_ids)
 */
class Offer extends AbstractEntity
{
    use SoftDeletes;
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;
    use HasTranslations;

    public const IMAGE_PATH = '/storage/images/offers/';
    public const INACTIVE = 'inactive';
    public const ACTIVE = 'active';
    public const ARCHIVED = 'archived';

    protected $fillable = [
        'title', 'is_private', 'agreement', 'description', 'url', 'status',
    ];
    protected $hidden = [
        'id', 'created_at', 'updated_at', 'deleted_at', 'pivot', 'has_direct_access', 'has_group_access',
        'is_temp_lead',
    ];
    protected $dates = ['deleted_at'];
    protected $appends = ['thumb_path', 'already_added', 'is_publisher_active'];

    public static $rules = [
        'title' => 'required|string|max:255',
        'agreement' => 'present|string|max:512',
        'description' => 'present|string|max:512',
        'translations' => 'present|array',
        'translations.*' => 'array|size:4',
        'translations.*.title' => 'required|string|max:255',
        'translations.*.agreement' => 'present|string|max:512',
        'translations.*.description' => 'present|string|max:512',
        'translations.*.locale_id' => 'required|numeric|exists:locales,id',
        'url' => 'url|max:255', // @todo add required
        'is_private' => 'required|in:0,1',
    ];

    public function getTitleAttribute($value)
    {
        return $this->getTranslatedAtribute('title', $value);
    }

    public function getAgreementAttribute($value)
    {
        return $this->getTranslatedAtribute('agreement', $value);
    }

    public function getDescriptionAttribute($value)
    {
        return $this->getTranslatedAtribute('description', $value);
    }

    public function getAlreadyAddedAttribute()
    {
        return array_get($this->getAttributes(), 'already_added', 0);
    }

    public function getIsPublisherActiveAttribute()
    {
        if ($this['is_private'] && !$this['has_direct_access'] && !$this['has_group_access']) {
            return 0;
        }

        return (int)($this['status'] === self::ACTIVE);
    }

    public function getThumbPathAttribute(): string
    {
        return $this->getThumbPath();
    }

    public function getThumbPath(): string
    {
        return self::IMAGE_PATH . $this->getAttribute('hash') . '.png';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
        static::addGlobalScope(new AlreadyAddedOfferScope);
    }

    public function offer_categories()
    {
        return $this->belongsToMany(OfferCategory::class);
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'target_geo')
            ->whereNull('target_geo.deleted_at');
    }

    public function landings()
    {
        return $this->hasMany(Landing::class);
    }

    public function transits()
    {
        return $this->hasMany(Transit::class);
    }

    public function translations()
    {
        return $this->hasMany(OfferTranslation::class);
    }

    public function publishers()
    {
        return $this->belongsToMany(
            User::class,
            'offer_publisher',
            'offer_id',
            'publisher_id'
        );
    }

    public function user_groups()
    {
        return $this->belongsToMany(
            UserGroup::class,
            'offer_user_group',
            'offer_id',
            'user_group_id'
        );
    }

    public function advertisers()
    {
        return $this->belongsToMany(User::class, 'offer_advertiser', 'offer_id', 'advertiser_id');
    }

    public function targets()
    {
        return $this->hasMany(Target::class);
    }

    public function offer_sources(): BelongsToMany
    {
        return $this->belongsToMany(OfferSource::class);
    }

    public function offer_requisites()
    {
        return $this->hasMany(OfferRequisiteTranslation::class);
    }

    public function email_integration()
    {
        return $this->hasOne(EmailIntegration::class);
    }

    public function sms_integration()
    {
        return $this->belongsTo(SmsIntegration::class);
    }

    public function offer_publisher()
    {
        return $this->hasMany(OfferPublisher::class);
    }

    public function offer_advertiser()
    {
        return $this->hasMany(OfferAdvertiser::class);
    }

    public function my_offers()
    {
        return $this->hasMany(MyOffer::class);
    }

    public function labels()
    {
        return $this->belongsToMany(OfferLabel::class)->withPivot(['available_at']);
    }

    public function scopeOnlyMy(Builder $query, User $user, ?int $only_my)
    {
        if ($only_my && $user['role'] === User::PUBLISHER) {
            $query->whereHas('my_offers', function (Builder $q) use ($user) {
                return $q->where('publisher_id', $user['id']);
            });
        }

        return $query;
    }

    public function scopePublic(Builder $builder)
    {
        return $builder->where('is_private', 0);
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('status', self::ACTIVE);
    }

    public function scopeExcludeArchived(Builder $builder)
    {
        return $builder->where('status', '!=', self::ARCHIVED);
    }

    public function scopeUserEnabled(Builder $query)
    {
        $user = \Auth::user();

        switch ($user['role']) {
            case User::PUBLISHER:
                return $query->haveAccess($user);

            case User::ADVERTISER:
                return $query->haveAccess($user);

            case User::ADMINISTRATOR:
                return $query;

            default:
                throw new UnknownUserRole($user['role']);
        }
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
                        'offers.*',
                        DB::raw('
                        (SELECT 1 
                            FROM `offer_publisher`
                            WHERE `offer_id` = `offers`.`id` 
                            AND `publisher_id` = ' . $user['id'] . ') as `has_direct_access`'),
                        DB::raw('
                        (SELECT 1 
                            FROM `offer_user_group`
                            WHERE `offer_id` = `offers`.`id` 
                            AND `user_group_id` = ' . $group_id . ') as `has_group_access`')
                    ]);

            case User::ADVERTISER:
                $ids = self::withoutGlobalScope(GlobalUserEnabledScope::class)
                    ->leftJoin('offer_advertiser as oa', 'oa.offer_id', '=', 'offers.id')
                    ->where('oa.advertiser_id', $user['id'])
                    ->get(['offers.id'])
                    ->pluck('id')
                    ->toArray();

                return $builder->whereIn('offers.id', $ids);
        }

        return $builder;
    }

    /**
     * Is used in not API places for determining publisher.
     */
    public function scopeIsActiveForPublisher(Builder $builder, User $publisher)
    {
        return $builder->haveAccess($publisher);
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

    public function scopeCategories(Builder $query, ?array $category_ids): Builder
    {
        return $query->when($category_ids, function (Builder $query) use ($category_ids) {
            return $query->whereHas('offer_categories', function (Builder $query) use ($category_ids) {
                return $query->whereIn('offer_category_id', $category_ids);
            });
        });
    }

    public function scopeSources(Builder $query, ?array $source_ids): Builder
    {
        return $query->when($source_ids, function (Builder $query) use ($source_ids) {
            return $query->whereHas('offer_sources', function (Builder $query) use ($source_ids) {
                return $query->whereIn('offer_source_id', $source_ids);
            });
        });
    }

    public function scopeCountries(Builder $query, ?array $country_ids): Builder
    {
        return $query->when($country_ids, function (Builder $query) use ($country_ids) {
            return $query->whereHas('countries', function (Builder $query) use ($country_ids) {
                return $query->whereIn('countries.id', $country_ids);
            });
        });
    }

    public function scopeWhereLabels(Builder $query, ?array $labels): Builder
    {
        return $query->when($labels, function (Builder $query) use ($labels) {
            return $query->whereHas('labels', function (Builder $query) use ($labels) {
                return $query->whereIn('offer_label_id', $labels);
            });
        });
    }

    public function scopeSearch(Builder $builder, $search)
    {
        if (empty($search)) {
            return $builder;
        }

        $search = urldecode($search);
        if (starts_with($search, 'hash:')) {
            $hash = mb_substr($search, 5);
            return $builder->where('hash', $hash);
        }

        /**
         * @var Client $client
         */
        $client = app(Client::class);
        $response = $client->search([
            'index' => ElasticSchema::INDEX,
            'type' => ElasticSchema::OFFER_TYPE,
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $search,
                        'type' => 'phrase_prefix',
                        'fields' => ['title^2', 'description']
                    ]
                ]
            ]
        ]);

        // fallback
        $offer_ids = [0];
        if (isset($response['hits']['total']) && $response['hits']['total'] > 0) {
            $offer_ids = collect($response['hits']['hits'])->pluck('_id')->transform(function ($id) {
                return (int)$id;
            })->toArray();
        }

        return $builder->whereIn('offers.id', $offer_ids);
    }

    /**
     * Получение оффера по его идентификатору
     *
     * @param int $offer_id
     * @param $with
     * @return Offer
     */
    public function getById(int $offer_id, array $with = []): self
    {
        return self::with($with)->findOrFail($offer_id);
    }

    /**
     * Получения случайного лендинга оффера для пользователя, по котором у нас нету данных
     *
     * @param Collection $offer_landings
     * @return int
     */
    public function getLandingForFallback(Collection $offer_landings): int
    {
        // @todo We need this?
        if ($offer_landings->count() < 1) {
            throw new ModelNotFoundException("Offer hasn't got any landings for show");
        }

//        $landings_ids = $offer_landings->pluck('id')->toArray();
//
//        shuffle($landings_ids);

        return $offer_landings->random(1);
    }

    /**
     * Получение реквизитов оффера для указанной локали
     *
     * @todo refactor this.
     *
     * @param $offer_id
     * @param $locale_id
     * @return mixed
     */
    public function getRequisiteByLocale($offer_id, $locale_id): self
    {
        //todo: перенести этот метод в соответствующую модель

        $info = self::with(['offer_requisites' => function ($query) use ($locale_id) {
            $query->where('locale_id', $locale_id);
        }])->find($offer_id);

        if (is_null($info) || !isset($info->offer_requisites[0]['content'])) {
            throw new ModelNotFoundException('Failed to get offer requisites');
        }

        return $info->offer_requisites[0]['content'];
    }

    public function saveImage(string $image)
    {
        file_force_contents(public_path($this->getThumbPath()), file_get_contents($image));
    }

    public static function getPermittedIdsForuser()
    {
        return self::get()->pluck('id')->toArray();
    }

    public function isPublic()
    {
        return !$this->is_private;
    }

    public static function rejectInactiveOffersForPublisher(Collection $offers)
    {
        return $offers->reject(function (Offer $offer, $key) {
            return $offer->isInactiveForPublisher();
        });
    }

    public function isInactiveForPublisher()
    {
        return !$this['is_publisher_active'];
    }

    public function syncTranslations(array $translations)
    {
        $actual_locale_ids = [];
        foreach ($translations as $translation) {
            $this
                ->translations()
                ->where('locale_id', $translation['locale_id'])
                ->updateOrCreate(['locale_id' => $translation['locale_id']], $translation);

            $actual_locale_ids[] = $translation['locale_id'];
        }

        // Удаляем локали, по которым не пришли переводы
        $this->translations()->whereNotIn('locale_id', $actual_locale_ids)->delete();
    }

    public function hasDefaultTarget(int $except_id = 0)
    {
        return Target::where('offer_id', $this['id'])
            ->default()
            ->when($except_id, function (Builder $builder) use ($except_id) {
                $builder->where('id', '!=', $except_id);
            })
            ->exists();
    }

    public function makeTargetAsIsNotDefault(int $except_id = 0)
    {
        return Target::where('offer_id', $this['id'])
            ->when($except_id, function (Builder $builder) use ($except_id) {
                $builder->where('id', '!=', $except_id);
            })
            ->update(['is_default' => 0]);
    }
}
