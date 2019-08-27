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
class SwiftRequisite extends AbstractEntity
{
    use IsEditable;
    use EloquentHashids;
    use SoftDeletes;
    use WhereUserIdScope;

    protected $fillable = [
        'user_id', 'payment_system_id', 'card_number', 'expires', 'birthday', 'document', 'country', 'street',
        'card_holder', 'phone', 'tax_id', 'is_editable'
    ];
    protected $hidden = ['id', 'user_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['title'];

    public function getTitleAttribute()
    {
        return $this->attributes['title'] = $this->attributes['card_number'];
    }

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function payment_system()
    {
        return $this->belongsTo(PaymentSystem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getHashidEncodingValue(Model $model)
    {
        return [$model->id, $model->user_id, $model->payment_system_id];
    }


    public function scopeUserEnabled(Builder $query)
    {
        return $query->where('user_id', \Auth::id());
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

        $card_number = $requisite['card_number'];

        $user_has_swift = (bool)$user->swift_requisites->count();

        if (!$user_has_swift && !empty($card_number)) {
            $epayments = [
                'user_id' => $user->id,
                'card_number' => $card_number,
                'card_holder' => $requisite['card_holder'],
                'expires' => $requisite['expires'],
                'birthday' => $requisite['birthday'],
                'document' => $requisite['document'],
                'country' => $requisite['country'],
                'street' => $requisite['street'],
                'phone' => $requisite['phone'],
                'tax_id' => $requisite['tax_id'],
            ];
            self::create(array_merge($epayments, [
                'payment_system_id' => PaymentSystem::SWIFT_RUB,
            ]));
            self::create(array_merge($epayments, [
                'payment_system_id' => PaymentSystem::SWIFT_USD,
            ]));
            self::create(array_merge($epayments, [
                'payment_system_id' => PaymentSystem::SWIFT_EUR,
            ]));

        } elseif ($user_has_swift) {

            foreach ($user->swift_requisites as $user_requisite) {
                if (!$user_requisite->isEditable()) {
                    continue;
                }

                if (empty($card_number)) {
                    $user_requisite->delete();
                } else {
                    $user_requisite->update([
                        'user_id' => \Auth::id(),
                        'card_number' => $card_number,
                        'card_holder' => $requisite['card_holder'],
                        'expires' => $requisite['expires'],
                        'birthday' => $requisite['birthday'],
                        'document' => $requisite['document'],
                        'country' => $requisite['country'],
                        'street' => $requisite['street'],
                        'phone' => $requisite['phone'],
                        'tax_id' => $requisite['tax_id'],
                    ]);
                }
            }
        }
    }

    public function disallowEdit()
    {
        // Отключаем редактирование реквизитов всех валют
        self::where('card_number', $this->card_number)->get()->each(function ($requisite) {
            $requisite->update(['is_editable' => 0]);
        });
    }
}

