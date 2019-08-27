<?php
declare(strict_types=1);

namespace App\Events\Flow;

use App\Events\Event;
use App\Models\Flow;
use Illuminate\Queue\SerializesModels;

class FlowCreated extends Event
{
    use SerializesModels;

    public $flow;

    public function __construct(Flow $flow)
    {
        $this->flow = $flow;
    }
}
