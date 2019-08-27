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
class EpaymentsRequisite extends AbstractEntity
{
    use IsEditable;
    use EloquentHashids;
    use SoftDeletes;
    use WhereUserIdScope;

    protected $fillable = ['user_id', 'payment_system_id', 'ewallet', 'is_editable'];
    protected $hidden = ['id', 'user_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['title'];

    public function getTitleAttribute()
    {
        return $this->attributes['title'] = $this->attributes['ewallet'];
    }

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
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
        $user = \Auth::user();

        $ewallet = $requisite['epayments'];

        $user_has_epayments = (bool)$user->epayments_requsites->count();

        if (!$user_has_epayments && !empty($ewallet)) {
            $epayments = [
                'user_id' => $user->id,
                'ewallet' => $ewallet,
            ];
            self::create(array_merge($epayments, [
                'payment_system_id' => PaymentSystem::EPAYMENTS_RUB,
            ]));
            self::create(array_merge($epayments, [
                'payment_system_id' => PaymentSystem::EPAYMENTS_USD,
            ]));
            self::create(array_merge($epayments, [
                'payment_system_id' => PaymentSystem::EPAYMENTS_EUR,
            ]));

        } elseif ($user_has_epayments) {

            foreach ($user->epayments_requsites as $user_requisite) {
                if (!$user_requisite->isEditable()) {
                    continue;
                }

                if (empty($ewallet)) {
                    $user_requisite->delete();
                } else {
                    $user_requisite->update(['ewallet' => $ewallet]);
                }
            }
        }
    }

    public function disallowEdit()
    {
        // Отключаем редактирование реквизитов всех валют
        self::where('ewallet', $this->ewallet)->get()->each(function ($requisite) {
            $requisite->update(['is_editable' => 0]);
        });
    }
}
