<?php
declare(strict_types=1);

namespace App\Models;

use App\Events\UserBalanceChanged;
use App\Models\Traits\RoleTrait;
use DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method $this search(?string $search_field, ?string $search)
 * @property AdvertiserProfile profile
 */
class Advertiser extends User
{
    use RoleTrait;

    public $userRole = 'advertiser';

    public function profile()
    {
        return $this->hasOne(AdvertiserProfile::class, 'user_id');
    }

    public function accounts()
    {
        return $this->hasMany(Account::class, 'user_id');
    }

    public function scopeSearch(Builder $builder, ?string $search_field, ?string $search)
    {
        if (empty($search_field) || empty($search)) {
            return $builder;
        }

        $search = urldecode($search);

        switch ($search_field) {
            case 'email':
                /**
                 * @var UserElasticQueries $queries
                 */
                $queries = app(UserElasticQueries::class);
                $user_ids = $queries->findIdsByEmail($search);

                return $builder->whereIn('id', $user_ids);
        }

        return $builder;
    }

    public function updateBalance(float $sum, int $currency_id): void
    {
        $this->getAccountByCurrencyId($currency_id)->update([
            'balance' => DB::raw("`balance`+ {$sum}")
        ]);

        event(new UserBalanceChanged($this['id']));
    }

    public function updateHold(float $sum, int $currency_id): void
    {
        $this->getAccountByCurrencyId($currency_id)->update([
            'hold' => DB::raw("`hold` + {$sum}")
        ]);
    }

    public function updateSystemBalance(float $sum, int $currency_id): void
    {
        $this->getAccountByCurrencyId($currency_id)->update([
            'system_balance' => DB::raw("`system_balance` + {$sum}")
        ]);
    }

    public function getAccountByCurrencyId(int $currency_id): Account
    {
        return $this->accounts()->where('currency_id', $currency_id)->firstOrFail();
    }
}
