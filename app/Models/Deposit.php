<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\GlobalUserEnabledScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EloquentHashids;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use DB;

/**
 * @property Advertiser advertiser
 * @method $this whereAdvertisers(array $advertiser_hashes)
 * @method $this whereCurrencies(array $currency_ids)
 */
class Deposit extends AbstractEntity
{
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;

    public const OTHER = 'other';

    protected $fillable = [
        'hash', 'advertiser_id', 'admin_id', 'currency_id', 'sum', 'description', 'replenishment_method',
        'invoice_file_id', 'contract_file_id', 'created_at'
    ];
    protected $hidden = ['id', 'advertiser_id', 'admin_id', 'updated_at'];
    protected $advertiser_hidden = ['id', 'advertiser_id', 'admin_id', 'admin', 'updated_at'];
    public static $rules = [
        'advertiser_id' => 'required|exists:users,id,role,' . User::ADVERTISER,
        'currency_id' => 'required|exists:currencies,id',
        'sum' => 'required|numeric|min:1',
        'description' => 'string',
        'replenishment_method' => 'in:cash,swift,epayments,webmoney,paxum,privat24,bitcoin,other',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function admin()
    {
        return $this->belongsTo(Administrator::class);
    }

    public function balance_transaction()
    {
        return $this->morphOne(BalanceTransaction::class, 'entity');
    }

    public function invoice_file()
    {
        return $this->belongsTo(StaticFile::class, 'invoice_file_id');
    }

    public function contract_file()
    {
        return $this->belongsTo(StaticFile::class, 'contract_file_id');
    }

    public function scopeUserEnabled(Builder $builder)
    {
        $user = \Auth::user();
        if ($user->isAdvertiser()) {
            return $builder->where('advertiser_id', $user['id']);
        }

        return $builder;
    }

    public function scopeWhereAdvertisers(Builder $builder, array $advertiser_hashes = [])
    {
        if (\is_array($advertiser_hashes) && \count($advertiser_hashes) > 0) {
            $builder->whereIn('advertiser_id', getIdsByHashes($advertiser_hashes));
        }

        return $builder;
    }

    public function scopeWhereCurrencies(Builder $builder, array $currency_ids = [])
    {
        if (!\is_array($currency_ids) || !\count($currency_ids)) {
            return $builder;
        }

        return $builder->whereIn('currency_id', $currency_ids);
    }
}
