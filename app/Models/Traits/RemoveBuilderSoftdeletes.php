<?php
declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\{
    Builder, Model
};

/**
 * Удаление softdeletes проверки из запроса
 */
trait RemoveBuilderSoftdeletes
{
    public function removeBuilderSoftdeletes(Builder $builder, Model $model)
    {
        // remove global scope
        $builder = $builder->withTrashed();

        // remove "where deleted_at = null"
        $wheres = $builder->getQuery()->wheres;

        foreach ($wheres as $key => $data) {
            if ($data['column'] == $model->getTable() . '.deleted_at' && $data['type'] == "Null") {
                unset($builder->getQuery()->wheres[$key]);
            }
        }

        return $builder;
    }
}