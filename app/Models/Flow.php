<?php
declare(strict_types=1);

namespace App\Models;

use DB;
use Hashids;
use Carbon\Carbon;
use Elasticsearch\Client;
use App\Classes\Statistics;
use App\Classes\ElasticSchema;
use App\Models\Scopes\GlobalUserEnabledScope;
use App\Models\Scopes\HideDraftStatus;
use App\Models\Scopes\ShowTrashedForAdmin;
use App\Models\Scopes\ShowTrashedForAdvertiser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\EloquentHashids;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use App\Exceptions\Hashids\NotDecodedHashException;

/**
 * @property FlowFlowWidget facebook_pixel_widgets
 * @property FlowFlowWidget yandex_metrika_widgets
 * @property FlowFlowWidget vk_widgets
 * @property FlowFlowWidget custom_html_widgets
 * @property FlowFlowWidget google_analitycs_widgets
 * @property FlowFlowWidget rating_mail_ru_widgets
 * @method $this withoutVirtual()
 * @method $this active()
 * @method $this whereCurrencies(?array $currency_ids)
 */
class Flow extends AbstractEntity
{
    use SoftDeletes;
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;

    public const DRAFT = 'draft';
    public const ACTIVE = 'active';
    public const ARCHIVED = 'archived';

    protected $fillable = [
        'hash', 'title', 'extra_flow_id', 'publisher_id', 'offer_id', 'target_id', 'is_detect_bot',
        'is_hide_target_list', 'is_cpc', 'cpc', 'cpc_lost', 'cpc_currency_id', 'is_noback', 'is_comebacker',
        'is_show_requisite', 'is_remember_landing', 'is_remember_transit', 'tb_url', 'group_id', 'status', 'is_virtual',
        'back_action_sec', 'back_call_btn_sec', 'back_call_form_sec', 'vibrate_on_mobile_sec',
    ];
    protected $hidden = ['id', 'publisher_id', 'offer_id', 'target_id', 'extra_flow_id', 'updated_at', 'deleted_at'];
    protected $dates = ['deleted_at'];
    public static $rules = [
        'title' => 'present|max:255',
        'landings' => 'required|array|min:1',
        'transits' => 'array',
        'target_hash' => 'required',
        'is_detect_bot' => 'required|in:0,1',
        'is_hide_target_list' => 'required|in:0,1',
        'is_noback' => 'required|in:0,1',
        'is_show_requisite' => 'required|in:0,1',
        'is_remember_landing' => 'required|in:0,1',
        'is_remember_transit' => 'required|in:0,1',
        'tb_url' => 'present|url',
        'back_action_sec' => 'present|min:0|max:100000',
        'back_call_btn_sec' => 'present|min:0|max:100000',
        'back_call_form_sec' => 'present|min:0|max:100000',
        'vibrate_on_mobile_sec' => 'present|min:0|max:100000',
        //'is_cpc' => 'required|in:0,1',
        //'cpc' => 'required_if:is_cpc,1|numeric|min:0',
        //'cpc_lost' => 'numeric',
        //'cpc_currency_id' => 'required|in:1,3,5,4',
        //'is_comebacker' => 'required|in:0,1',

    ];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ShowTrashedForAdmin());
        static::addGlobalScope(new ShowTrashedForAdvertiser());
        static::addGlobalScope(new HideDraftStatus);
        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public static function getHashidEncodingValue(Model $model)
    {
        return [$model->id, $model->publisher_id];
    }

    public function target()
    {
        return $this->belongsTo(Target::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function publisher()
    {
        return $this->belongsTo(PublisherProfile::class, 'publisher_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function extra_flow()
    {
        return $this->belongsTo(self::class);
    }

    public function postbacks()
    {
        return $this->hasMany(Postback::class, 'flow_id');
    }

    public function group()
    {
        return $this->belongsTo(FlowGroup::class);
    }

    public function day_statistics()
    {
        [$app_tz, $user_tz] = (new self)->getTzs();

        return $this
            ->hasMany(PublisherStatistic::class)
            ->select(
                DB::raw('SUM(`hosts`) as hosts'),
                DB::raw('SUM(`payout`) as payout'),
                DB::raw('SUM(`leads`) as leads'),
                DB::raw('SUM(`approved_leads`) as approved_leads'),
                DB::raw("HOUR(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}')) as hour"),
                DB::raw('`flow_id` as flow_id')
            )
            ->where('datetime', '>', Carbon::create()->subHours(24)->toDateTimeString())
            ->groupBy(DB::raw("flow_id, HOUR(CONVERT_TZ(`datetime`, '{$app_tz}', '{$user_tz}'))"));
    }

    public function landings()
    {
        return $this->belongsToMany(Landing::class, 'flow_landing');
    }

    public function transits()
    {
        return $this->belongsToMany(Transit::class, 'flow_transit');
    }

    public function flow_landings()
    {
        return $this->hasMany(FlowLanding::class);
    }

    public function flow_transits()
    {
        return $this->hasMany(FlowTransit::class);
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(FlowFlowWidget::class);
    }

    public function yandex_metrika_widgets()
    {
        return $this
            ->hasMany(FlowFlowWidget::class)
            ->where('flow_widget_id', FlowWidget::YANDEX_METRIKA);
    }

    public function facebook_pixel_widgets()
    {
        return $this
            ->hasMany(FlowFlowWidget::class)
            ->where('flow_widget_id', FlowWidget::FACEBOOK_PIXEL);
    }

    public function custom_html_widgets()
    {
        return $this
            ->hasMany(FlowFlowWidget::class)
            ->where('flow_widget_id', FlowWidget::CUSTOM_CODE);
    }

    public function vk_widgets()
    {
        return $this
            ->hasMany(FlowFlowWidget::class)
            ->where('flow_widget_id', FlowWidget::VK_WIDGET);
    }

    public function google_analitycs_widgets()
    {
        return $this
            ->hasMany(FlowFlowWidget::class)
            ->where('flow_widget_id', FlowWidget::GOOGLE_ANALITYCS);

    }

    public function rating_mail_ru_widgets()
    {
        return $this
            ->hasMany(FlowFlowWidget::class)
            ->where('flow_widget_id', FlowWidget::RATING_MAIL_RU);
    }

    public function scopeWithoutTrashed(Builder $builder)
    {
        return $builder->whereNull('deleted_at');
    }

    public function scopeWithoutVirtual(Builder $builder)
    {
        return $builder->where('is_virtual', 0);
    }

    public function scopeUserEnabled(Builder $builder)
    {
        $user = \Auth::user();
        if ($user->isPublisher()) {
            $builder->where('publisher_id', $user['id']);
        }

        return $builder;
    }

    public function scopeOfferHashes(Builder $builder, ?array $offer_hashes)
    {
        return $builder->when($offer_hashes, function (Builder $builder) use ($offer_hashes) {
            $builder->whereIn('offer_id', getIdsByHashes($offer_hashes));
        });
    }

    public function scopeGroupHashes(Builder $builder, ?array $group_hashes)
    {
        return $builder->when($group_hashes, function (Builder $builder) use ($group_hashes) {
            $builder->whereIn('group_id', getIdsByHashes($group_hashes));
        });
    }

    public function scopeWhereCurrencies(Builder $builder, ?array $currency_ids)
    {
        return $builder->when($currency_ids, function (Builder $builder) use ($currency_ids) {
            $builder->whereHas('offer', function (Builder $builder) use ($currency_ids) {
                return $builder->whereIn('currency_id', $currency_ids);
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
            'type' => ElasticSchema::FLOW_TYPE,
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $search,
                        'type' => 'phrase_prefix',
                        'fields' => ['title']
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

        return $builder->whereIn('id', $offer_ids);
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('status', self::ACTIVE);
    }

    public function getById(int $flow_id, array $relations = []): Flow
    {
        return self::with($relations)->findOrFail($flow_id);
    }

    public function getByHash(string $hash, array $relations = []): Flow
    {
        $id = $this->getIdFromHash($hash);

        return $this->getById($id, $relations);
    }

    /**
     * Получение идентификатора потока по его hash
     *
     * @param $flow_hash
     * @return mixed
     * @throws NotDecodedHashException
     */
    public function getIdFromHash($flow_hash)
    {
        $flow_data = Hashids::decode($flow_hash);
        if (\count($flow_data) < 1) {
            throw new NotDecodedHashException('Incorrect flow hash');
        }

        $flow_id = $flow_data[0];

        return $flow_id;
    }

    public static function getFallbackOrCreate(Offer $offer, Target $target): Flow
    {
        return self::where('publisher_id', config('env.fallback_publisher_id'))
            ->where('offer_id', $offer->id)
            ->where('target_id', $target->id)
            ->firstOrCreate([
                'publisher_id' => (int)config('env.fallback_publisher_id'),
                'offer_id' => $offer->id,
                'target_id' => $target->id,
            ], [
                'title' => $offer->title . ' / ' . $target->title,
                'publisher_id' => (int)config('env.fallback_publisher_id'),
                'offer_id' => $offer->id,
                'target_id' => $target->id,
                'status' => self::ACTIVE,
            ]);
    }

    public function isFallbackPublisher(): bool
    {
        return $this->publisher_id === (int)config('env.fallback_publisher_id');
    }

    public function getDefaultTitle()
    {
        return trans('flows.default_title', [
            'offer_title' => $this->offer['title'],
            'date' => Carbon::create()->format('d.m.Y'),
        ]);
    }

    /**
     * Метод для добавления пустых значений статистики по дням
     *
     * @param array $flow
     * @return array
     */
    public static function normalizeDayStatistics(array $flow)
    {
        if (!isset($flow['day_statistics'])) {
            throw new \LogicException('N + 1 when normalize day statistics of flow.');
        }

        $yesterday_hour = Carbon::now()->subHours(23)->format('H');
        $today_hour = Carbon::now()->format('H');
        $hours = array_merge(Statistics::getYesterdayHours($yesterday_hour), Statistics::getTodayHours($today_hour));

        $day_statistics = [];
        foreach ($hours as $hour) {
            $day_stat = collect($flow['day_statistics'])->where('hour', (int)$hour)->first();
            if (!is_null($day_stat)) {
                $day_statistics[] = $day_stat;
            } else {
                $day_statistics[] = [
                    'hour' => $hour,
                    'hosts' => 0,
                    'payout' => 0,
                    'leads' => 0,
                    'approved_leads' => 0,
                ];
            }
        }

        return $day_statistics;
    }
}
