<?php
declare(strict_types=1);

namespace App\Events\Lead;

use App\Models\Lead;
use Illuminate\Queue\SerializesModels;

class LeadChangedSubstatus extends LeadStateEvent
{
    use SerializesModels;

    /**
     * @var string
     */
    public $postback_event;
    /**
     * @var null|string
     */
    public $foreign_changed_at;

    public function __construct(Lead $lead, ?string $foreign_changed_at)
    {
        parent::__construct($lead);

        $this->foreign_changed_at = $foreign_changed_at;
    }
}
