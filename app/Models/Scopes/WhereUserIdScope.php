<?php
declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait WhereUserIdScope
{
    public function scopeWhereUser(Builder $builder, User $user)
    {
        return $builder->where('user_id', $user['id']);
    }
}
