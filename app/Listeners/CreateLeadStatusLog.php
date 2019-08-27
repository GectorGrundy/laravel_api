<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Models\Lead;
use App\Models\LeadStatusLog;
use App\Events\Lead\LeadStateEvent;

class CreateLeadStatusLog
{
    /**
     * @param LeadStateEvent $event
     */
    public function handle(LeadStateEvent $event)
    {
        $this->create($event->lead, $event->foreign_changed_at ?? null);
    }

    public function create(Lead $lead, ?string $foreign_changed_at = null)
    {
        $last_log = LeadStatusLog::findLastForLead($lead);

        if ($last_log['status'] === $lead['status'] && $last_log['sub_status_id'] === $lead['sub_status_id']) {
            return;
        }

        LeadStatusLog::create([
            'lead_id' => $lead['id'],
            'integration_id' => $lead['integration_id'],
            'integration_type' => Lead::INTEGRATION,
            'status' => $lead['status'],
            'sub_status_id' => $lead['sub_status_id'],
            'external_key' => $lead['external_key'],
            'foreign_changed_at' => $foreign_changed_at,
        ]);
    }
}
