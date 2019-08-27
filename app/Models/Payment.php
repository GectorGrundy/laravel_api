<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\GlobalUserEnabledScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\EloquentHashids;
use App\Models\Traits\DynamicHiddenVisibleTrait;

/**
 * @method $this whereStatus(string $status)
 * @method $this currencies(array $currency_ids)
 * @method $this wherePublishers(array $publisher_hashes = [])
 * @method $this paymentSystems(array $payment_systems = [])
 */
class Payment extends AbstractEntity
{
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;
    use SoftDeletes;

    public const PENDING = 'pending';
    public const CANCELLED = 'cancelled';
    public const ACCEPTED = 'accepted';
    public const PAID = 'paid';

    protected $fillable = [
        'hash', 'user_role', 'processed_user_id', 'paid_user_id', 'user_id', 'requisite_id', 'requisite_type', 'status',
        'type', 'currency_id', 'payout', 'balance_payout', 'comission', 'description'
    ];
    protected $hidden = ['id', 'user_role', 'user_id', 'requisite_id', 'updated_at', 'deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function requisite()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processed_user()
    {
        return $this->belongsTo(User::class, 'processed_user_id');
    }

    public function paid_user()
    {
        return $this->belongsTo(User::class, 'paid_user_id');
    }

    public function scopeUserEnabled(Builder $query)
    {
        return $query->haveAccess();
    }

    public function scopeHaveAccess(Builder $query): Builder
    {
        $user = \Auth::user();

        if ($user->isPublisher()) {
            $query->where('user_id', $user['id']);
        }

        return $query;
    }

    public function scopeCurrencies(Builder $query, array $currency_ids = []): Builder
    {
        return $query->when($currency_ids, function (Builder $query) use ($currency_ids) {
            return $query->whereIn('currency_id', $currency_ids);
        });
    }

    public function scopePaymentSystems(Builder $query, array $payment_systems = []): Builder
    {
        return $query->when($payment_systems, function (Builder $query) use ($payment_systems) {

            return $query->whereIn('requisite_type', $payment_systems);
        });
    }

    public function scopeWhereStatus(Builder $builder, $status)
    {
        return $builder->when($status, function (Builder $builder) use ($status) {
            $builder->where('payments.status', $status);
        });
    }

    public function scopeWherePublishers(Builder $builder, array $publisher_hashes = [])
    {
        return $builder->when($publisher_hashes, function (Builder $builder) use ($publisher_hashes) {
            $builder->whereIn('payments.user_id', getIdsByHashes($publisher_hashes));
        });
    }

    public static function getRequisiteType(PaymentSystem $payment_system)
    {
        switch ((int)$payment_system->id) {
            case PaymentSystem::WEBMONEY_RUB:
            case PaymentSystem::WEBMONEY_USD:
            case PaymentSystem::WEBMONEY_EUR:
                return 'webmoney';

            case PaymentSystem::PAXUM_RUB:
            case PaymentSystem::PAXUM_USD:
            case PaymentSystem::PAXUM_EUR:
                return 'paxum';

            case PaymentSystem::EPAYMENTS_RUB:
            case PaymentSystem::EPAYMENTS_USD:
            case PaymentSystem::EPAYMENTS_EUR:
                return 'epayments';

            case PaymentSystem::CASH_RUB:
            case PaymentSystem::CASH_USD:
            case PaymentSystem::CASH_EUR:
                return 'cash';

            case PaymentSystem::SWIFT_RUB:
            case PaymentSystem::SWIFT_USD:
            case PaymentSystem::SWIFT_EUR:
                return 'swift';

            default:
                throw new \LogicException('Unsupported payment system for payment.');

        }
    }
}
