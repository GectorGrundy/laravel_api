<?php
declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\Traits\RemoveBuilderSoftdeletes;
use App\Models\User;
use Illuminate\Database\Eloquent\{
    Model, Builder, Scope
};

/**
 *  Отображение записей для роли Рекламодатель, которые были удалены
 */
class ShowTrashedForAdvertiser implements Scope
{
    use RemoveBuilderSoftdeletes;

    public function apply(Builder $builder, Model $model)
    {
        if (\Auth::user()['role'] === User::ADVERTISER) {
            return $this->removeBuilderSoftdeletes($builder, $model);
        }
    }
}
