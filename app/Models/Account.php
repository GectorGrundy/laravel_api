<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\DynamicHiddenVisibleTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method $this whereUser(User $user)
 * @method $this active()
 */
class Account extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;

    protected $fillable = ['user_id', 'currency_id', 'balance', 'hold', 'system_balance', 'is_active'];
    protected $hidden = ['id', 'user_id', 'hold', 'system_balance', 'created_at', 'updated_at'];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function scopeWhereUser(Builder $builder, User $user)
    {
        return $builder->where('user_id', $user['id']);
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('is_active', 1);
    }
}
