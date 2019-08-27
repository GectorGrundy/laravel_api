<?php
declare(strict_types=1);

namespace App\Exceptions\TargetGeo;

use RuntimeException;

class CannotDetectTargetGeo extends RuntimeException
{
    public function __construct(int $target_id)
    {
        parent::__construct("Невозможно определить гео цель для заданной цели [{$target_id}]");
    }
}
