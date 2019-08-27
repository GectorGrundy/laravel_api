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
class PaxumRequisite extends AbstractEntity
{
    use IsEditable;
    use EloquentHashids;
    use SoftDeletes;
    use WhereUserIdScope;

    protected $fillable = ['user_id', 'payment_system_id', 'email', 'is_editable'];
    protected $hidden = ['id', 'user_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['title'];

    public function getTitleAttribute()
    {
        return $this->attributes['title'] = $this->attributes['email'];
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
        $email = $requisite['paxum'];

        $user_has_paxum = (bool)$user->paxum_requsites->count();

        if (!$user_has_paxum && !empty($email)) {
            $paxum = [
                'user_id' => $user->id,
                'email' => $email,
            ];
            self::create(array_merge($paxum, [
                'payment_system_id' => PaymentSystem::PAXUM_RUB,
            ]));
            self::create(array_merge($paxum, [
                'payment_system_id' => PaymentSystem::PAXUM_USD,
            ]));
            self::create(array_merge($paxum, [
                'payment_system_id' => PaymentSystem::PAXUM_EUR,
            ]));
        } elseif ($user_has_paxum) {

            foreach ($user->paxum_requsites as $user_requisite) {
                if (!$user_requisite->isEditable()) {
                    continue;
                }

                if (empty($email)) {
                    $user_requisite->delete();
                } else {
                    $user_requisite->update(['email' => $email]);
                }
            }
        }
    }

    public function disallowEdit()
    {
        // Отключаем редактирование реквизитов всех валют
        self::where('email', $this->email)->get()->each(function ($requisite) {
            $requisite->update(['is_editable' => 0]);
        });
    }
}
