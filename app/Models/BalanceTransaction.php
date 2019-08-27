<?php
declare(strict_types=1);

namespace App\Models;

use DB;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use Hashids;
use Carbon\Carbon;
use App\Models\Scopes\GlobalUserEnabledScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EloquentHashids;

/**
 * @property Advertiser $advertiser
 * @method Builder whereUsers(array $user_ids)
 * @method Builder whereLeadOffers(array $offer_hashes)
 */
class BalanceTransaction extends AbstractEntity
{
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;

    public const ADVERTISER_HOLD = 'advertiser.hold';
    public const ADVERTISER_UNHOLD = 'advertiser.unhold';
    public const ADVERTISER_DEPOSIT = 'advertiser.deposit';
    public const ADVERTISER_WRITE_OFF = 'advertiser.write-off';
    public const ADVERTISER_CANCEL = 'advertiser.cancel';

    public const PUBLISHER_HOLD = 'publisher.hold';
    public const PUBLISHER_UNHOLD = 'publisher.unhold';
    public const PUBLISHER_CANCEL = 'publisher.cancel';
    public const PUBLISHER_WITHDRAW = 'publisher.withdraw';
    public const PUBLISHER_WITHDRAW_CANCEL = 'publisher.withdraw_cancel';
    public const PUBLISHER_CUSTOM = 'publisher.custom';
    public const DEPOSIT = 'deposit';
    public const LEAD = 'lead';

    protected $fillable = [
        'id', 'hash', 'user_id', 'admin_id', 'entity_type', 'entity_id', 'currency_id', 'type',
        'balance_sum', 'system_balance_sum', 'hold_sum', 'description', 'created_at',
    ];
    protected $hidden = ['id', 'user_id', 'admin_id', 'entity_id', 'updated_at'];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);

        // Hide manually lead completion transactions
        static::addGlobalScope('system_balance_sum', function (Builder $builder) {
            $builder
                ->where('balance_sum', '!=', 0)
                ->orWhere('hold_sum', '!=', 0)
                ->where('system_balance_sum', '=', 0);
        });
    }

    public static function getHashidEncodingValue(Model $model)
    {
        return [$model->id, $model->user_id];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'entity_id');
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class, 'entity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Administrator::class, 'admin_id');
    }

    public function entity()
    {
        return $this->morphTo();
    }

    public function scopeUserEnabled(Builder $query)
    {
        $user = \Auth::user();

        if (!$user->isAdmin()) {
            return $query->where('user_id', $user['id']);
        }

        return $query;
    }

    public function scopeWhereUsers(Builder $builder, array $user_ids = [])
    {
        if (\Auth::user()->isAdmin() && is_array($user_ids) && count($user_ids) > 0) {
            $builder->whereIn('user_id', $user_ids);
        }

        return $builder;
    }

    public function scopeWhereTypes(Builder $builder, array $types)
    {
        if (\is_array($types) && \count($types) > 0) {
            $builder->whereIn('type', $types);
        }

        return $builder;
    }

    public function scopeSearch(Builder $builder, ?string $search_field, ?string $search)
    {
        if (\is_null($search_field) || \is_null($search)) {
            return $builder;
        }

        switch ($search_field) {
            case 'transaction_hash':
                $id = Hashids::decode($search)[0] ?? -1;
                return $builder->where('id', $id);

            case 'lead_hash':
                $lead_id = Hashids::decode($search)[0] ?? -1;
                return $builder->whereHas('lead', function ($builder) use ($lead_id) {
                    return $builder->where('id', $lead_id);
                });

            default:
                return $builder;
        }
    }

    public function scopeWhereCurrencies(Builder $builder, array $currency_ids = [])
    {
        if (!\count($currency_ids)) {
            return $builder;
        }

        return $builder->whereIn('currency_id', $currency_ids);
    }

    public function scopeWhereLeadOffers(Builder $builder, array $offer_hashes = [])
    {
        if (!\count($offer_hashes)) {
            return $builder;
        }

        return $builder->whereHas('lead', function (Builder $builder) use ($offer_hashes) {
            return $builder->whereIn('offer_id', getIdsByHashes($offer_hashes));
        });
    }

    public function scopeWhereLeadCountries(Builder $builder, array $country_ids = [])
    {
        if (!\count($country_ids)) {
            return $builder;
        }

        return $builder->whereHas('lead', function (Builder $builder) use ($country_ids) {
            return $builder->whereIn('country_id', $country_ids);
        });
    }

    /**
     * Запись финансовой операции подтверждения лида
     *
     * @param Lead $lead
     * @return BalanceTransaction
     */
    public static function insertPublisherHold(Lead $lead): self
    {
        return DB::transaction(function () use ($lead) {

            $hold_sum = (float)$lead['payout'];

            (new PublisherProfile())->updateHold($lead['publisher_id'], $hold_sum, $lead['currency_id']);

            return self::create([
                'user_id' => $lead['publisher_id'],
                'currency_id' => $lead['currency_id'],
                'type' => self::PUBLISHER_HOLD,
                'entity_type' => self::LEAD,
                'entity_id' => $lead['id'],
                'balance_sum' => 0,
                'hold_sum' => $hold_sum,
            ]);
        });
    }

    /**
     * Запись финансовой операции при переводе холда на баланс
     *
     * @param Lead $lead
     * @return BalanceTransaction
     */
    public static function insertPublisherUnhold(Lead $lead): self
    {
        return DB::transaction(function () use ($lead) {

            $balance_sum = (float)$lead['payout'];
            $hold_sum = (float)"-{$lead['payout']}";

            (new PublisherProfile())->updateBalance($lead['publisher_id'], $balance_sum, $lead['currency_id']);
            (new PublisherProfile())->updateHold($lead['publisher_id'], $hold_sum, $lead['currency_id']);

            return self::create([
                'user_id' => $lead['publisher_id'],
                'currency_id' => $lead['currency_id'],
                'type' => self::PUBLISHER_UNHOLD,
                'entity_type' => self::LEAD,
                'entity_id' => $lead['id'],
                'balance_sum' => $balance_sum,
                'hold_sum' => $hold_sum,
            ]);
        });
    }

    public static function insertPublisherCancel(Lead $lead, string $from_status): ?self
    {
        if ($from_status !== Lead::APPROVED || !$lead->integrated()) {
            return null;
        }

        return DB::transaction(function () use ($lead) {

            if ($lead->onHold()) {
                $balance_sum = 0;
                $hold_sum = (float)"-{$lead['payout']}";
            } else {
                $balance_sum = (float)"-{$lead['payout']}";
                $hold_sum = 0;
            }

            if ($balance_sum) {
                (new PublisherProfile())->updateBalance($lead['publisher_id'], $balance_sum, $lead['currency_id']);
            }
            if ($hold_sum) {
                (new PublisherProfile())->updateHold($lead['publisher_id'], $hold_sum, $lead['currency_id']);
            }

            return self::create([
                'user_id' => $lead['publisher_id'],
                'currency_id' => $lead['currency_id'],
                'type' => self::PUBLISHER_CANCEL,
                'entity_type' => self::LEAD,
                'entity_id' => $lead['id'],
                'balance_sum' => $balance_sum,
                'hold_sum' => $hold_sum
            ]);
        });
    }

    public static function insertAdvertiserDeposit(Deposit $deposit, string $description, ?string $created_at = null): self
    {
        return DB::transaction(function () use ($deposit, $description, $created_at) {

            $balance_sum = (float)$deposit['sum'];

            $deposit->advertiser->updateBalance($balance_sum, (int)$deposit['currency_id']);
            $deposit->advertiser->updateSystemBalance($balance_sum, (int)$deposit['currency_id']);

            return self::create([
                'currency_id' => $deposit['currency_id'],
                'type' => self::ADVERTISER_DEPOSIT,
                'entity_type' => self::DEPOSIT,
                'entity_id' => $deposit['id'],
                'user_id' => $deposit['advertiser_id'],
                'admin_id' => \Auth::user()['id'],
                'balance_sum' => $balance_sum,
                'system_balance_sum' => $balance_sum,
                'description' => $description,
                'created_at' => $created_at ?? Carbon::now()->toDateTimeString(),
            ]);
        });
    }

    /**
     * Запись финансовой операции при определении лида адвертизеру
     *
     * @param Lead $lead
     * @return BalanceTransaction
     */
    public static function insertAdvertiserHold(Lead $lead): self
    {
        return DB::transaction(function () use ($lead) {

            $hold_sum = (float)$lead['advertiser_payout'];

            $lead->advertiser->updateHold($hold_sum, $lead['advertiser_currency_id']);

            return self::create([
                'user_id' => $lead['advertiser_id'],
                'currency_id' => $lead['advertiser_currency_id'],
                'type' => self::ADVERTISER_HOLD,
                'entity_type' => self::LEAD,
                'entity_id' => $lead['id'],
                'hold_sum' => $hold_sum,
            ]);
        });
    }

    /**
     * Запись финансовой операции при подтверждении лида
     *
     * @param Lead $lead
     * @param string $from_status
     * @return BalanceTransaction
     */
    public static function insertAdvertiserUnhold(Lead $lead, string $from_status): self
    {
        return DB::transaction(function () use ($lead, $from_status) {

            $balance_sum = -(float)$lead['advertiser_payout'];
            $lead->advertiser->updateBalance($balance_sum, (int)$lead['advertiser_currency_id']);

            $hold_sum = $from_status === Lead::NEW ? (float)"-{$lead['advertiser_payout']}" : 0;
            if ($hold_sum) {
                $lead->advertiser->updateHold($hold_sum, $lead['advertiser_currency_id']);
            }

            $system_balance_sum = 0;
            if ($lead->completed()) {
                $system_balance_sum = -(float)$lead['advertiser_payout'];
                $lead->advertiser->updateSystemBalance($system_balance_sum, $lead['advertiser_currency_id']);
            }

            return self::create([
                'user_id' => $lead['advertiser_id'],
                'currency_id' => $lead['advertiser_currency_id'],
                'type' => self::ADVERTISER_UNHOLD,
                'entity_type' => self::LEAD,
                'entity_id' => $lead['id'],
                'balance_sum' => $balance_sum,
                'system_balance_sum' => $system_balance_sum,
                'hold_sum' => $hold_sum,
            ]);
        });
    }

    /**
     * Запись финансовой операции при ручном взаиморасчете с рекламодателем за лид
     *
     * @param Lead $lead
     * @return BalanceTransaction
     */
    public static function insertAdvertiserUnholdWhenManuallyCompleted(Lead $lead): self
    {
        return DB::transaction(function () use ($lead) {

            throw_if(!$lead->completed(), new \BadMethodCallException());

            $system_balance_sum = -(float)$lead['advertiser_payout'];

            $lead->advertiser->updateSystemBalance($system_balance_sum, $lead['advertiser_currency_id']);

            return self::create([
                'user_id' => $lead['advertiser_id'],
                'currency_id' => $lead['advertiser_currency_id'],
                'type' => self::ADVERTISER_UNHOLD,
                'entity_type' => self::LEAD,
                'entity_id' => $lead['id'],
                'balance_sum' => 0,
                'system_balance_sum' => $system_balance_sum,
                'hold_sum' => 0,
            ]);
        });
    }

    public static function insertAdvertiserCancel(Lead $lead, string $from_status): ?self
    {
        if ($from_status === Lead::CANCELLED || $from_status === Lead::TRASHED || !$lead->integrated()) {
            return null;
        }

        return DB::transaction(function () use ($lead, $from_status) {

            $balance_sum = $from_status === Lead::APPROVED ? (float)$lead['advertiser_payout'] : 0;
            $hold_sum = $from_status === Lead::NEW ? (float)"-{$lead['advertiser_payout']}" : 0;

            if ($balance_sum) {
                $lead->advertiser->updateBalance($balance_sum, (int)$lead['advertiser_currency_id']);
                $lead->advertiser->updateSystemBalance($balance_sum, (int)$lead['advertiser_currency_id']);
            }

            if ($hold_sum) {
                $lead->advertiser->updateHold($hold_sum, $lead['advertiser_currency_id']);
            }

            if ($balance_sum || $hold_sum) {
                return self::create([
                    'user_id' => $lead['advertiser_id'],
                    'currency_id' => $lead['advertiser_currency_id'],
                    'type' => self::ADVERTISER_CANCEL,
                    'entity_type' => self::LEAD,
                    'entity_id' => $lead['id'],
                    'balance_sum' => $balance_sum,
                    'system_balance_sum' => $balance_sum,
                    'hold_sum' => $hold_sum,
                ]);
            }
        });
    }

    public static function insertPublisherWithdraw(PaymentSystem $payment_system, $requsite, Payment $payment): self
    {
        return DB::transaction(function () use ($payment_system, $requsite, $payment) {

            $balance_sum = (float)"-{$payment['balance_payout']}";

            (new PublisherProfile())->updateBalance($payment['user_id'], $balance_sum, $payment['currency_id']);

            return self::create([
                'type' => self::PUBLISHER_WITHDRAW,
                'currency_id' => $payment['currency_id'],
                'user_id' => $payment['user_id'],
                'entity_type' => self::getEntityType($payment_system),
                'entity_id' => $requsite['id'],
                'balance_sum' => $balance_sum,
                'hold_sum' => 0,
                'description' => 'Ondemand payment',
            ]);
        });
    }

    public static function insertPublisherWithdrawCancel(Payment $payment): self
    {
        return DB::transaction(function () use ($payment) {

            (new PublisherProfile)->updateBalance(
                (int)$payment['user_id'],
                (float)$payment['balance_payout'],
                (int)$payment['currency_id']
            );

            return self::create([
                'type' => self::PUBLISHER_WITHDRAW_CANCEL,
                'currency_id' => $payment['currency_id'],
                'user_id' => $payment['user_id'],
                'entity_type' => $payment['requisite_type'],
                'entity_id' => $payment['requisite_id'],
                'balance_sum' => $payment['balance_payout'],
                'hold_sum' => 0,
                'description' => 'Cancelled ondemand payment',
            ]);
        });
    }

    public static function insertAdvertiserWriteOff(
        Advertiser $advertiser,
        float $balance_sum,
        int $currency_id,
        string $description
    ): self
    {
        return DB::transaction(function () use ($advertiser, $balance_sum, $currency_id, $description) {
            $balance_sum = -$balance_sum;

            $advertiser->updateBalance($balance_sum, $currency_id);
            $advertiser->updateSystemBalance($balance_sum, $currency_id);

            return self::create([
                'type' => self::ADVERTISER_WRITE_OFF,
                'currency_id' => $currency_id,
                'user_id' => $advertiser['id'],
                'admin_id' => \Auth::user()['id'],
                'balance_sum' => $balance_sum,
                'system_balance_sum' => $balance_sum,
                'description' => $description,
            ]);
        });
    }

    public static function getEntityType(PaymentSystem $payment_system)
    {
        return strtolower($payment_system['title']);
    }
}

