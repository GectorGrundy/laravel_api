<?php
declare(strict_types=1);

namespace App\Events\TargetGeo;

use App\Events\Event;
use App\Models\TargetGeo;

class TargetGeoEdited extends Event
{
    /**
     * @var TargetGeo
     */
    public $target_geo;

    public function __construct(TargetGeo $target_geo)
    {
        $this->target_geo = $target_geo;
    }
}
