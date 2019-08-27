<?php
declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use App\Exceptions\Hashids\NotDecodedHashException;
use Illuminate\Database\Eloquent\{
    Model, Builder, ModelNotFoundException, Relations\HasOne
};
use Illuminate\Support\Collection;
use App\Models\Traits\EloquentHashids;
use App\Models\Traits\DynamicHiddenVisibleTrait;

/**
 * Observers:
 *
 * @todo product_hash в $appends всегда попадает в dirty поля, и с этим нужно что-то сделать
 * - updated: TrackingNumberSetObserver
 * - creating: FillFieldsFromInfoField
 */
class Order extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;
    use EloquentHashids;

    public const PHONE_FIXED_LINE = 0;
    public const PHONE_MOBILE = 1;
    public const PHONE_UNKNOWN = 10;
    public const CALL_CONTACT_TYPE = 'call';
    public const LINE_CONTACT_TYPE = 'line';
    public const WECHAT_CONTACT_TYPE = 'wechat';
    public const VIBER_CONTACT_TYPE = 'viber';
    public const TELEGRAM_CONTACT_TYPE = 'telegram';
    public const WHATSAPP_CONTACT_TYPE = 'whatsapp';
    public const MESSENGER_CONTACT_TYPE = 'messenger';
    public const CONTACT_TYPES = [
        self::CALL_CONTACT_TYPE, self::LINE_CONTACT_TYPE, self::WECHAT_CONTACT_TYPE, self::VIBER_CONTACT_TYPE,
        self::TELEGRAM_CONTACT_TYPE, self::WHATSAPP_CONTACT_TYPE, self::MESSENGER_CONTACT_TYPE
    ];

    protected $hidden = ['id'];
    protected $fillable = [
        'integration_id', 'name', 'phone', 'info', 'address', 'email', 'products', 'is_corrected',
        'history', 'number_type_id', 'payment_external_key', 'integration_external_key', 'integrated_at',
        'tracking_number', 'tracked_at', 'payment_checkout_url', 'is_first_email_notified', 'is_first_email_opened',
        'is_tracking_number_sms_notified', 'country_id', 'product_cost', 'product_cost_sign', 'delivery_cost',
        'delivery_cost_sign', 'total_cost', 'total_cost_sign', 'last_name', 'street', 'house', 'apartment', 'zipcode',
        'city', 'full_name', 'target_geo_region_id', 'tax_cost', 'tax_cost_sign', 'document', 'comment', 'contact_type', 'custom'
    ];
    protected $appends = ['info_array', 'product_hash', 'products_array', 'history_array', 'number_type'];

    public function getInfoArrayAttribute()
    {
        if (isset($this->attributes['info'])) {
            return json_decode($this->attributes['info'], true);
        }
    }

    public function getHistoryArrayAttribute()
    {
        if (isset($this->attributes['history'])) {
            return $this->attributes['history_array'] = json_decode($this->attributes['history'], true);
        }
    }

    public function getProductHashAttribute()
    {
        $products = $this->attributes['products'] ?? '{}';

        // @todo check this
        if ($products !== '{}' && $products !== 'null') {

            return $this->attributes['product_hash'] = array_keys(
                json_decode($products, true)
            )[0];
        }
    }

    public function getProductsArrayAttribute()
    {
        if (isset($this->attributes['products'])) {
            return $this->attributes['products_array'] = json_decode($this->attributes['products'], true);
        }
    }

    public function setNameAttribute($name)
    {
        $this->attributes['name'] = trim($name);

        $this->setFullName();
    }

    public function setLastNameAttribute($last_name)
    {
        $this->attributes['last_name'] = trim($last_name);

        $this->setFullName();
    }

    public function setFullName()
    {
        $full_name = $this->attributes['name'];

        if (!empty($this->attributes['last_name'])) {
            $full_name .= ' ' . $this->attributes['last_name'];
        }

        $this->attributes['full_name'] = $full_name;
    }

    public function getNumberTypeAttribute()
    {
        switch ($this->attributes['number_type_id']) {
            case self::PHONE_FIXED_LINE:
                return $this->number_type = 'FIXED_LINE';

            case self::PHONE_MOBILE:
                return $this->number_type = 'MOBILE';

            case self::PHONE_UNKNOWN:
            default:
                return $this->number_type = 'UNKNOWN';
        }
    }

    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class);
    }

    public function scopeDoNotIntegrated(Builder $builder)
    {
        return $builder->whereNull('integrated_at');
    }

    public function scopeIntegrated(Builder $builder)
    {
        return $builder->whereNotNull('integrated_at');
    }

    public function scopeWhereIntegratedAt(Builder $query, string $date_from, string $date_to): Builder
    {
        if ($date_from === '' || $date_from === null) {
            $date_from = date('Y-m-d', time());
        }

        if ($date_to === '' || $date_to === null) {
            $date_to = date('Y-m-d', time());
        }

        $query->where(\DB::raw('DATE(orders.integrated_at)'), '>=', $date_from)
            ->where(\DB::raw('DATE(orders.integrated_at)'), '<=', $date_to);

        return $query;
    }

    public function getById(int $id, array $relations = array()): self
    {
        return self::with($relations)->findOrFail($id);
    }

    public function getByHash(string $hash, array $relations = array())
    {
        $id = $this->getIdFromHash($hash);

        return $this->getById($id, $relations);
    }

    public function getIdFromHash($hash)
    {
        $data = \Hashids::decode($hash);
        if (count($data) < 1) {
            throw new NotDecodedHashException('Incorrect order hash');
        }

        return $data[0];
    }

    public function createNew($params): self
    {
        return self::create($params);
    }

    /**
     * Получение списка заказов указанной интеграции, лиды которых были подтверждены
     *
     * @param int $integration_id
     * @return Collection
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function getApprovedByIntegrationId(int $integration_id): Collection
    {
        $orders = self::where('integration_id', $integration_id)
            ->doNotIntegrated()
            ->whereHas('lead', function ($query) {
                $query->approved();
            })
            ->get();

        if ($orders === null) {
            throw new ModelNotFoundException();
        }

        return $orders;
    }

    /**
     * Медот для установки заказу признака "Интегрированности" с внешней интеграцией-складом
     *
     * @return Order
     */
    public function setAsIntegrated(): Order
    {
        // @todo Костыль из-за $appends поля product_hash, которое не дает обновлять $this
        $order = self::find($this->id);

        $order->update([
            'integrated_at' => Carbon::now()
        ]);

        return $order;
    }

    public static function isPhoneDailyUnique(string $phone, int $except)
    {
        $day_ago_datetime = Carbon::now()->subHours(24)->toDateTimeString();

        return !self::where('id', '!=', $except)
            ->where('phone', $phone)
            ->createdFrom($day_ago_datetime)
            ->exists();
    }
}
