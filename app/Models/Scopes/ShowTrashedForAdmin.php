<?php
declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\Traits\RemoveBuilderSoftdeletes;
use App\Models\User;
use Illuminate\Database\Eloquent\{
    Model, Builder, Scope
};

/**
 * Отображение записей для роли Администратор, которые были удалены
 */
class ShowTrashedForAdmin implements Scope
{
    use RemoveBuilderSoftdeletes;

    public function apply(Builder $builder, Model $model)
    {
        if (\Auth::user()['role'] === User::ADMINISTRATOR) {
            return $this->removeBuilderSoftdeletes($builder, $model);
        }
    }
}
