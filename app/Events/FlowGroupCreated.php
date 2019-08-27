<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\FlowGroup;

class FlowGroupCreated extends Event
{
    public $flow_group;

    public function __construct(FlowGroup $flow_group)
    {
        $this->flow_group = $flow_group;
    }
}
