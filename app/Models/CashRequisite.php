<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EloquentHashids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель "заглушка" для возможности использовать платежную систему "Cash"
 * @method $this currency(int $currency_id)
 */
class CashRequisite extends AbstractEntity
{
    use EloquentHashids;
    use SoftDeletes;

    protected $guarded = ['*'];
    public $timestamps = false;

    public function payment_system()
    {
        return $this->belongsTo(PaymentSystem::class);
    }

    public static function getHashidEncodingValue(Model $model)
    {
        return [$model->id, $model->payment_system_id];
    }

    public function scopeCurrency(Builder $query, int $currency_id)
    {
        return $query->whereHas('payment_system', function ($q) use ($currency_id) {
            return $q->currency($currency_id);
        });
    }

}

