<?php
declare(strict_types=1);

namespace App\Events\TargetGeoRule;

use App\Events\Event;
use App\Models\TargetGeoRule;

class TargetGeoRuleDeleted extends Event
{
    /**
     * @var TargetGeoRule
     */
    public $target_geo_rule;

    public function __construct(TargetGeoRule $target_geo_rule)
    {
        $this->target_geo_rule = $target_geo_rule;
    }
}
