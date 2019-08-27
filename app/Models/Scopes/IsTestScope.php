<?php
declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\{
    Model, Builder, Scope
};

class IsTestScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('is_test', 0);
    }
}
