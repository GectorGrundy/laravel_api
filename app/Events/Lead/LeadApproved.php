<?php
declare(strict_types=1);

namespace App\Events\Lead;

use App\Models\Lead;
use App\Models\PostbackOut;
use Illuminate\Queue\SerializesModels;

class LeadApproved extends LeadStateEvent
{
    use SerializesModels;

    /**
     * @var string
     */
    public $postback_event;

    public function __construct(Lead $lead, string $from_status)
    {
        parent::__construct($lead, $from_status);

        $this->postback_event = PostbackOut::LEAD_APPROVE;
    }
}
