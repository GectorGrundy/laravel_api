<?php
declare(strict_types=1);

namespace App\Events\Lead;

use App\Models\Lead;
use Illuminate\Queue\SerializesModels;

class LeadIntegrated extends LeadStateEvent
{
    use SerializesModels;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }
}
