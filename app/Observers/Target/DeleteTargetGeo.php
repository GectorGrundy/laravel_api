<?php
declare(strict_types=1);

namespace App\Observers\Target;

use App\Models\Target;
use App\Models\TargetGeo;

/**
 * Удаление гео целей перед удалением цели
 */
class DeleteTargetGeo
{
    public function deleting(Target $target)
    {
        TargetGeo::where('target_id', $target->id)->get()->each(function (TargetGeo $target_geo) {
            $target_geo->delete();
        });
    }
}
