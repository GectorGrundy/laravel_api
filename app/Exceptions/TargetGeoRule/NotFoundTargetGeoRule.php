<?php
declare(strict_types=1);

namespace App\Exceptions\TargetGeoRule;

use RuntimeException;

class NotFoundTargetGeoRule extends RuntimeException
{
    public function __construct(int $target_geo_id)
    {
        parent::__construct("Нету подходящих правил для интеграции лида гео цели \"{$target_geo_id}\"");
    }
}
