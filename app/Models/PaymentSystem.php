<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\GlobalUserEnabledScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentSystem extends AbstractEntity
{
    public const WEBMONEY = 'webmoney';
    public const EPAYMENTS = 'epayments';
    public const PAXUM = 'paxum';
    public const WEBMONEY_RUB = 1;
    public const WEBMONEY_USD = 2;
    public const WEBMONEY_EUR = 3;

    public const PAXUM_RUB = 4;
    public const PAXUM_USD = 5;
    public const PAXUM_EUR = 6;

    public const EPAYMENTS_RUB = 7;
    public const EPAYMENTS_USD = 8;
    public const EPAYMENTS_EUR = 9;

    public const CASH_RUB = 10;
    public const CASH_USD = 11;
    public const CASH_EUR = 12;

    public const SWIFT_RUB = 13;
    public const SWIFT_USD = 14;
    public const SWIFT_EUR = 15;

    public const ACTIVE = 'active';
    public const STOPPED = 'stopped';

    protected $fillable = ['title', 'status', 'percentage_comission', 'fixed_comission', 'min_payout'];
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function publishers()
    {
        return $this->belongsToMany(Publisher::class);
    }

    public function webmoney_requisite()
    {
        return $this->hasOne(WebmoneyRequisite::class);
    }

    public function paxum_requisite()
    {
        return $this->hasOne(PaxumRequisite::class);
    }

    public function epayments_requisite()
    {
        return $this->hasOne(EpaymentsRequisite::class);
    }

    public function swift_requisite()
    {
        return $this->hasOne(SwiftRequisite::class);
    }

    public function cash_requisite()
    {
        return $this->hasOne(CashRequisite::class);
    }

    public function scopeCurrency(Builder $builder, int $currency_id)
    {
        return $builder->where('currency_id', $currency_id);
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('status', self::ACTIVE);
    }

    public function scopeUserEnabled(Builder $query)
    {
        $user = \Auth::user();
        if ($user->isPublisher()) {
            return $query->haveAccess();
        }

        return $query;
    }

    public function scopeHaveAccess(Builder $builder): Builder
    {
        $user = \Auth::user();

        $ids = self::withoutGlobalScope(GlobalUserEnabledScope::class)
            ->leftJoin('payment_system_publisher as psp', 'psp.payment_system_id', '=', 'payment_systems.id')
            ->where('psp.publisher_id', $user['id'])
            ->orWhere('payment_systems.status', self::ACTIVE)
            ->get(['payment_systems.id'])
            ->pluck('id')->toArray();

        return $builder->whereIn('id', $ids);
    }

    public function scopeWithWebmoneyRequisites(Builder $builder, User $user): Builder
    {
        return $builder
            ->with(['webmoney_requisite' => function ($builder) use ($user) {
                return $builder->where('user_id', $user['id']);
            }])
            ->whereIn('id', [self::WEBMONEY_RUB, self::WEBMONEY_USD, self::WEBMONEY_EUR]);
    }

    public function scopeWithCashRequisite(Builder $builder, $currency_id): Builder
    {
        switch ($currency_id) {
            case Currency::RUB_ID:
                $builder->where('id', self::CASH_RUB);
                break;

            case Currency::USD_ID:
                $builder->where('id', self::CASH_USD);
                break;

            case Currency::EUR_ID:
                $builder->where('id', self::CASH_EUR);
                break;

            default:
                throw new \LogicException();
        }

        return $builder->with(['cash_requisite']);
    }

    public function scopeWithPaxumRequisites(Builder $builder, User $user): Builder
    {
        return $builder
            ->with(['paxum_requisite' => function ($builder) use ($user) {
                return $builder->where('user_id', $user['id']);
            }])
            ->whereIn('id', [self::PAXUM_RUB, self::PAXUM_USD, self::PAXUM_EUR]);
    }

    public function scopeWithEpaymentsRequisites(Builder $builder, User $user): Builder
    {
        return $builder
            ->with(['epayments_requisite' => function ($builder) use ($user) {
                return $builder->where('user_id', $user['id']);
            }])
            ->whereIn('id', [self::EPAYMENTS_RUB, self::EPAYMENTS_USD, self::EPAYMENTS_EUR]);
    }

    public function scopeWithSwiftRequisites(Builder $builder, User $user): Builder
    {
        return $builder
            ->with(['swift_requisite' => function ($builder) use ($user) {
                return $builder->where('user_id', $user['id']);
            }])
            ->whereIn('id', [self::SWIFT_RUB, self::SWIFT_USD, self::SWIFT_EUR]);
    }

    public function getComission(float $payout): float
    {
        return $this->fixed_comission + $this->calcPercentageComission($payout);
    }

    public function enableComissionToPayout(float $payout, float $comission): float
    {
        return $payout - $comission;
    }

    private function calcPercentageComission(float $payout): float
    {
        return $payout / 100 * (float)$this->percentage_comission;
    }

    public static function cashIdByCurrencyId($currency_id)
    {
        switch ($currency_id) {
            case Currency::RUB_ID:
                return self::CASH_RUB;

            case Currency::USD_ID:
                return self::CASH_USD;

            case Currency::EUR_ID:
                return self::CASH_EUR;

            default:
                throw new \UnexpectedValueException();

        }
    }

    public function isCash()
    {
        return $this->id === self::CASH_RUB || $this->id === self::CASH_USD || $this->id === self::CASH_EUR;
    }
}
