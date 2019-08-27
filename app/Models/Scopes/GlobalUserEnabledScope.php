<?php
declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\{
    Model, Builder, Scope
};

class GlobalUserEnabledScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = \Auth::user();
        if (!is_null($user)) {
            $builder->userEnabled();
        }
    }
}
