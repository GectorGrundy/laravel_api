<?php
declare(strict_types=1);

namespace App\Events\Lead;

use App\Models\Lead;
use Illuminate\Queue\SerializesModels;

class LeadReverted extends LeadStateEvent
{
    use SerializesModels;

    /**
     * @var string
     */
    public $postback_event;

    public function __construct(Lead $lead, string $from_status)
    {
        parent::__construct($lead, $from_status);
    }
}
