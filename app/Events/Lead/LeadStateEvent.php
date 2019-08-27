<?php
declare(strict_types=1);

namespace App\Events\Lead;

use App\Events\Event;
use App\Models\Lead;

abstract class LeadStateEvent extends Event
{
    /**
     * @var Lead
     */
    public $lead;
    /**
     * @var string
     */
    public $from_status;

    public function __construct(Lead $lead, ?string $from_status = null)
    {
        $this->lead = $lead;
        $this->from_status = $from_status;
    }
}