<?php
declare(strict_types=1);

namespace App\Models;

use DB;
use App\Events\UserBalanceChanged;
use App\Models\Traits\DynamicHiddenVisibleTrait;

class PublisherProfile extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;

    protected $fillable = [
        'user_id', 'support_id', 'full_name', 'skype', 'telegram', 'phone', 'tl', 'balance_usd', 'balance_rub',
        'balance_eur', 'hold_usd', 'hold_rub', 'hold_eur', 'api_key', 'comment', 'data_type'
    ];
    protected $hidden = ['id', 'user_id', 'support_id', 'comment', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function publisher()
    {
        return $this->hasOne(Publisher::class, 'id', 'user_id');
    }

    public function updateBalance(int $user_id, float $sum, int $currency_id): void
    {
        $balance_field = 'balance_' . strtolower(self::getCodeById($currency_id));

        DB::statement(
            "UPDATE `{$this->getTable()}`
                      SET `{$balance_field}` = (`{$balance_field}` + {$sum})
                      WHERE `user_id`        = {$user_id}
                      LIMIT 1;"
        );

        event(new UserBalanceChanged($user_id));
    }

    public function updateHold(int $user_id, float $sum, int $currency_id): void
    {
        $hold_field = 'hold_' . strtolower(self::getCodeById($currency_id));

        DB::statement(
            "UPDATE `{$this->getTable()}`
                      SET `{$hold_field}`    = (`{$hold_field}` + {$sum})
                      WHERE `user_id`        = {$user_id}
                      LIMIT 1;"
        );
    }

    /**
     * Восстановление баланса и холда по транзакциям
     *
     * @param int $user_id
     */
    public function restoreBalanceByBalanceTransactions(int $user_id): void
    {
        $profile = self::whereUserId($user_id)->firstOrFail();

        $profile->update(['balance_rub' => 0, 'balance_usd' => 0, 'balance_eur' => 0]);

        // RUB
        $rub = BalanceTransaction::select(
            DB::raw('SUM(`balance_sum`) as `balance_sum`'),
            DB::raw('SUM(`hold_sum`) as `hold_sum`')
        )
            ->where('user_id', $user_id)
            ->where('currency_id', Currency::RUB_ID)
            ->groupBy('currency_id')
            ->first();

        // USD
        $usd = BalanceTransaction::select(
            DB::raw('SUM(`balance_sum`) as `balance_sum`'),
            DB::raw('SUM(`hold_sum`) as `hold_sum`')
        )
            ->where('user_id', $user_id)
            ->where('currency_id', Currency::USD_ID)
            ->groupBy('currency_id')
            ->first();

        // EГК
        $eur = BalanceTransaction::select(
            DB::raw('SUM(`balance_sum`) as `balance_sum`'),
            DB::raw('SUM(`hold_sum`) as `hold_sum`')
        )
            ->where('user_id', $user_id)
            ->where('currency_id', Currency::EUR_ID)
            ->groupBy('currency_id')
            ->first();

        $profile->update([
            'balance_rub' => $rub['balance_sum'] ?? 0,
            'balance_usd' => $usd['balance_sum'] ?? 0,
            'balance_eur' => $eur['balance_sum'] ?? 0,
            'hold_rub' => $rub['hold_sum'] ?? 0,
            'hold_usd' => $usd['hold_sum'] ?? 0,
            'hold_eur' => $eur['hold_sum'] ?? 0,
        ]);
    }

    public static function getBalanceFieldByCurrencyId(int $currency_id): string
    {
        return 'balance_' . strtolower(self::getCodeById($currency_id));
    }

    public static function getHoldFieldByCurrencyId(int $currency_id): string
    {
        return 'hold_' . strtolower(self::getCodeById($currency_id));
    }

    public static function getCodeById($id): ?string
    {
        switch ((int)$id) {
            case Currency::RUB_ID:
                return 'RUB';

            case Currency::USD_ID:
                return 'USD';

            case Currency::EUR_ID:
                return 'EUR';

            default:
                throw new \LogicException('Unknown currency id - ' . $id);
        }
    }
}
