<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\GlobalUserEnabledScope;
use App\Models\Scopes\WhereUserIdScope;
use App\Models\Traits\IsEditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\EloquentHashids;

/**
 * @method $this currency(int $currency_id)
 * @method $this whereUser(User $user)
 */
class WebmoneyRequisite extends AbstractEntity
{
    use IsEditable;
    use EloquentHashids;
    use SoftDeletes;
    use WhereUserIdScope;

    protected $fillable = ['user_id', 'payment_system_id', 'purse', 'is_editable'];
    protected $hidden = ['id', 'user_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['title'];

    public function getTitleAttribute()
    {
        return $this->attributes['title'] = $this->attributes['purse'];
    }

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function scopePaymentSystem(Builder $builder, int $payment_system_id)
    {
        return $builder->where('payment_system_id', $payment_system_id);
    }

    public static function getHashidEncodingValue(Model $model)
    {
        return [$model->id, $model->user_id, $model->payment_system_id];
    }

    public function payment_system()
    {
        return $this->belongsTo(PaymentSystem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUserEnabled(Builder $builder)
    {
        $user = \Auth::user();
        if (!$user->isAdmin()) {
            $builder->where('user_id', $user['id']);
        }

        return $builder;
    }

    public function scopeCurrency(Builder $query, int $currency_id)
    {
        return $query->whereHas('payment_system', function ($q) use ($currency_id) {
            return $q->currency($currency_id);
        });
    }

    public function upgradeOrCreate(array $requisite)
    {
        $requisite = self::paymentSystem($requisite['payment_system_id'])->user(\Auth::user())->first();

        if (!is_null($requisite)) {

            if (!$requisite->isEditable()) {
                return;
            }

            if (empty($requisite['purse'])) {
                $requisite->delete();
            } else {
                $requisite->update(['purse' => $requisite['purse']]);
            }
        } elseif (!empty($requisite['purse'])) {
            self::create([
                'user_id' => \Auth::id(),
                'payment_system_id' => $requisite['payment_system_id'],
                'purse' => $requisite['purse']
            ]);
        }
    }

    public function upgradeOrCreateWmr(array $requisite)
    {
        $user = \Auth::user();

        if (is_null($user->wmr) && !empty($requisite['wmr'])) {
            self::create([
                'user_id' => $user->id,
                'payment_system_id' => PaymentSystem::WEBMONEY_RUB,
                'purse' => $requisite['wmr']
            ]);
        }

        if (!\is_null($user->wmr) && $user->wmr->isEditable()) {

            if (empty($requisite['wmr'])) {
                $user->wmr->delete();
            } else {
                $user->wmr->update(['purse' => $requisite['wmr']]);
            }
        }
    }

    public function upgradeOrCreateWmz(array $requisite)
    {
        $user = \Auth::user();

        if (is_null($user->wmz) && !empty($requisite['wmz'])) {
            self::create([
                'user_id' => $user->id,
                'payment_system_id' => PaymentSystem::WEBMONEY_USD,
                'purse' => $requisite['wmz']
            ]);
        }

        if (!is_null($user->wmz) && $user->wmz->isEditable()) {

            if (empty($requisite['wmz'])) {
                $user->wmz->delete();
            } else {
                $user->wmz->update(['purse' => $requisite['wmz']]);
            }
        }
    }

    public function upgradeOrCreateWme(array $requisite)
    {
        $user = \Auth::user();

        if (is_null($user->wme) && !empty($requisite['wme'])) {
            self::create([
                'user_id' => $user->id,
                'payment_system_id' => PaymentSystem::WEBMONEY_EUR,
                'purse' => $requisite['wme']
            ]);
        }

        if (!\is_null($user->wme) && $user->wme->isEditable()) {

            if (empty($requisite['wme'])) {
                $user->wme->delete();
            } else {
                $user->wme->update(['purse' => $requisite['wme']]);
            }
        }
    }

    public function disallowEdit()
    {
        $this->update(['is_editable' => 0]);
    }
}

