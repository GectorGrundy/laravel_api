<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Models\Lead;
use App\Jobs\LeadExchange;

class IntegrateCodLead
{
    public function handle(Lead $lead)
    {
        if ($lead['type'] !== Lead::COD_TYPE) {
            return;
        }

        if ($lead['is_valid_phone']) {
            $integration_delay = config('env.integration_valid_lead_delay', 0) * 60;
        } else {
            $integration_delay = config('env.integration_invalid_lead_delay', 0) * 60;
        }

        if (
            starts_with($lead->order['name'], 'test') ||
            $lead['origin'] === Lead::API_ORIGIN
        ) {
            $integration_delay = 0;
        }

        $job = new LeadExchange($lead['id'], $lead['target_geo_id']);
        $job->delay($integration_delay)->onQueue(config('queue.app.integration'));
        dispatch($job);
    }
}
