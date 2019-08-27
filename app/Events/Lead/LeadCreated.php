<?php
declare(strict_types=1);

namespace App\Events\Lead;

use App\Models\Lead;
use App\Models\PostbackOut;
use Illuminate\Queue\SerializesModels;

class LeadCreated extends LeadStateEvent
{
    use SerializesModels;

    /**
     * @var string
     */
    public $postback_event;

    public function __construct(Lead $lead, string $postback_event = null)
    {
        parent::__construct($lead);

        $this->postback_event = PostbackOut::LEAD_ADD;
    }
}
