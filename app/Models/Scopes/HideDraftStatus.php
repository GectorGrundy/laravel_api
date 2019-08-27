<?php
declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\{
    Model, Builder, Scope
};

/**
 * Не выводить сущности с status=draft
 */
class HideDraftStatus implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder->where('status', '!=', 'draft');
    }
}
